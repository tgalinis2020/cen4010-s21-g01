<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use ThePetPark\Services;
use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Schema\ReferenceTable;
use ThePetPark\Library\Graph\Schema\Relationship as R;

use function explode;
use function count;
use function key;
use function next;
use function current;

/**
 * Generates a query using the information provided in the request's
 * attributes. Returns a JSON-API document.
 */
final class Resolve
{
    /** @var \ThePetPark\Library\Graph\Schema\Container */
    private $schemas;

    /** @var string */
    private $baseUrl;

    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn, Graph\Schema\Container $schemas, string $url)
    {
        $this->conn = $conn;
        $this->schemas = $schemas;
        $this->baseUrl = $url;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        parse_str($request->getUri()->getQuery(), $params);

        $resource = $request->getAttribute('resource');
        $id = $request->getAttribute('id');
        $relationship = $request->getAttribute('relationship');

        if ($this->schemas->has($resource) === false) {
            return $response->withStatus(404);
        }

        $qb = $this->conn->createQueryBuilder();
        $data = [];
        $amount = R::MANY;

        // This *should* be defined in the dependency container and injected
        // when needed, but it's fine to leave it here for now since it's
        // only ever used here.
        $driver = new Graph\Drivers\Doctrine\Driver($qb, [
            'defaultPageSize' => 12,
            'features' => [
                Graph\Drivers\Doctrine\Features\SparseFieldsets::class,
                Graph\Drivers\Doctrine\Features\Filtering::class,
                Graph\Drivers\Doctrine\Features\Sorting::class,
                Graph\Drivers\Doctrine\Features\Pagination\CursorStrategy::class,
                Graph\Drivers\Doctrine\Features\Pagination\PagerStrategy::class,
                Graph\Drivers\Doctrine\Features\Pagination\OffsetLimitStrategy::class,
            ]
        ]);

        $refs = new ReferenceTable($this->schemas, $resource, $driver);

        $base = $refs->getBaseRef();

        $driver->apply($params, $this->schemas, $refs);

        // BEGIN(resolve)
        if ($id !== null) {

            $qb->andWhere($qb->expr()->eq(
                $base . '.' . $base->getSchema()->getId(),
                $qb->createNamedParameter($id)
            ));

            if ($relationship !== null) {

                if ($base->getSchema()->hasRelationship($relationship) === false) {
                    return $response->withStatus(400);
                }
                
                // Promote relationship to the context of the query.
                // Note that applied filters may have already resolved
                // related resources.
                $base = $refs->has($relationship)
                    ? $refs->get($relationship)
                    : $refs->resolve($relationship, $base);

                if ($base->getType() & R::ONE) {
                    $amount = R::ONE;
                }

            } else {

                $amount = R::ONE;
            }
        
        }

        // Base reference could have changed if a relationship is available.
        // Also, Driver::setSource is executed when setting the base or a parent
        // reference.
        $refs->setBaseRef($base);

        // BEGIN(data retrieval and serialization)
        $links = [];
        
        $links['self'] = $this->baseUrl . '/' . $resource;

        if ($id !== null) {
            $links['self'] .= '/' . $id;
        }

        if ($relationship !== null) {
            $links['related'] = $links['self'];
            $links['self']   .= '/' . $relationship;
        }

        $document = ['jsonapi' => '1.0', 'links' => $links, 'data' => null];
        $data     = [];
        $items    = []; 
        $schema   = $base->getSchema();
        $type     = $schema->getType();
        $attrs    = $schema->getAttributes();
        $prefix   = $base . '_';
        $items    = [];
        $resolved = [$schema->getType() => []]; // resource type => id list mappings

        foreach ($qb->execute()->fetchAll(FetchMode::ASSOCIATIVE) as $rec) {
            $resourceID = $rec[$prefix . 'id'];
            $item = ['type' => $type, 'id' => $resourceID, 'attributes' => []];

            foreach ($attrs as $attr) {
                $item['attributes'][$attr] = $rec[$prefix . $attr];
            }

            $items[$resourceID] = $item;
        }
        
        $data[$base->getRef()] = $items;
        $rowCount = count($items);
        // END(data retrieval and serialization)
        // END(resolve)

        // BEGIN(related resource inclusion)
        if ($rowCount > 0 && isset($params['include'])) {

            // TODO:    this could be a feature (that addiditonally accepts main
            //          data and the document as input)

            // Reset fields but keep source table(s), conditions and base IDs.
            // Create a new query based on retrieved data. Keep the base
            // resource's ID since it will be needed for relationship
            // propagation.
            $qb->resetQueryParts(['select', 'distinct', 'orderBy'])
                ->setFirstResult(0)
                ->setMaxResults(null)
                ->addSelect(sprintf(
                    '%1$s.%2$s %1$s_%3$s',
                    $base,
                    $base->getSchema()->getId(),
                    'id'
                ));

            // No need to constrain included data if previous query yielded only
            // one result.
            if ($rowCount > 1) {
                $end     = end($data[$base->getRef()]);
                $start   = reset($data[$base->getRef()]);
                $firstID = (int) $start['id'];
                $lastID  = (int) $end['id'];

                // If sorting is applied, it won't always be the case that the
                // first ID is in the first record and the last ID is in the
                // last record. Need to do a linear scan to pluck out the min
                // and max.
                // Although IDs *should* be sequential, they could be of any
                // type. A comparator should be used to compare IDs.
                if (isset($params['sort'])) {
                    foreach ($data[$base->getRef()] as $rec) {
                        if (((int) $rec['id']) < $firstID) {
                            $firstID = (int) $rec['id'];
                        } elseif ((int) $rec['id'] > $lastID) {
                            $lastID = (int) $rec['id'];
                        }
                    }
                }

                $idField = $base . '.' . $base->getSchema()->getId();

                // Only include data relevant to the previously fetched data.
                $qb->andWhere($qb->expr()->andX(
                    $qb->expr()->gte($idField, $firstID),
                    $qb->expr()->lte($idField, $lastID)
                ));
            }

            foreach (explode(',', $params['include']) as $included) {
                $ref = $base;
                $token = $delim = '';
                
                foreach (explode('.', $included) as $relationship) {
                    $token .= $delim . $relationship;
                    $relatedRef = null;

                    if ($ref->getSchema()->hasRelationship($relationship) === false) {

                        // If provided token isn't a valid relationship,
                        // stop here. TODO: might be worth deferring error
                        // handling to another controller.
                        return $response->withStatus(400);
                    
                    }
    
                    // A resource may have already been resolved (joined in
                    // the query) if it was used in a filter.
                    if ($refs->has($token)) {
                        $relatedRef = $refs->get($token);
                    } else {
                        $relatedRef = $refs->resolve($relationship, $ref);
                    }
                    
                    $refs->setParentRef($relatedRef, $ref);

                    $ref = $relatedRef;
                    $delim = '.';
                }

                $resolved[$ref->getSchema()->getType()] = [];
            }
            
            // TODO:    Like before, data retrieval and serialization is done
            //          at the same time. Even if it might have slight overhead,
            //          it might be worth simply retrieving data first THEN
            //          serialize it into a JSON API document.
            //          This is kind of hard to look at!
            //
            // TODO:    With the current strategy, it is impossible to paginate
            //          included data since all of it is fetched in one big query.
            //          In hindsight, running a query for each relationship
            //          might not incur that much overhead and allows for more
            //          flexibility with how included data is fetched.
            //          Also, a check for duplicates would not be necessary!
            $records = $qb->execute()->fetchAll(FetchMode::ASSOCIATIVE);

            foreach ($refs->getParentRefs() as $refID => $parent) {
                $data[$refID] = [];
                $ref = $refs->getRefById($refID);
                $prefix = $ref . '_';

                foreach ($records as $rec) {
                    $resourceID = $rec[$prefix . 'id'];

                    // BEGIN(relationship processing)
                    $parentID = $rec[$parent . '_id'];
                    $resourceIdentifier = [
                        'type'  => $schema->getType(),
                        'id'    => $resourceID,
                    ];

                    if (isset($data[$parent->getRef()][$parentID]['relationships']) === false) {
                        $data[$parent->getRef()][$parentID]['relationships'] = [];
                    }

                    if (isset($data[$parent->getRef()][$parentID]['relationships'][$ref->getName()]) == false) {
                        $data[$parent->getRef()][$parentID]['relationships'][$ref->getName()] = [
                            'links' => [
                                'self' => sprintf(
                                    '%s/%s/%s/relationships/%s',
                                    $this->baseUrl,
                                    $parent->getSchema()->getType(),
                                    $parentID,
                                    $ref->getName()
                                ),

                                'related' => sprintf(
                                    '%s/%s/%s/%s',
                                    $this->baseUrl,
                                    $parent->getSchema()->getType(),
                                    $parentID,
                                    $ref->getName()
                                ),
                            ],

                            'data' => [],
                        ];
                    }

                    // Propagate relationship to parent.
                    if ($ref->getType() & R::ONE) {
                        $data[$parent->getRef()][$parentID]['relationships'][$ref->getName()]['data']   = $resourceIdentifier;
                    } else {
                        $data[$parent->getRef()][$parentID]['relationships'][$ref->getName()]['data'][] = $resourceIdentifier;
                    }
                    // END(relationship processing)
                    
                    // Ignore duplicates. There may be many of them!
                    if (isset($data[$refID][$resourceID]) === false) {
                        $schema = $ref->getSchema();
                        $data[$refID][$resourceID] = [
                            'type'          => $schema->getType(),
                            'id'            => $resourceID,
                            'attributes'    => [],
                        ];
                        
                        // Add attributes to resource object.
                        foreach ($schema->getAttributes() as $attr) {
                            $data[$refID][$resourceID]['attributes'][$attr] = $rec[$prefix . $attr];
                        }
                    }
                }
            }

            // Point to included data, which should start immediately after the
            // first reference.
            $document['included'] = [];
            reset($data);
            $included = next($data); // main data is the first element, skip it
            
            // Note: some included resources may contain duplicate data.
            // This is why the "resolved" map exists.
            // 
            // e.g. fetching article authors and article comment authors -- an
            // article author can also be a comment author.            
            while ($included !== false) {
                foreach ($included as $key => $resource) {
                    $type = $resource['type'];

                    if (isset($resolved[$type][$key]) === false) {
                        $document['included'][] = $resource;
                        $resolved[$type][$key] = true;
                    }
                }

                $included = next($data);
            }
        }

        // TODO:    Add top-level pagination links.
        //          This depends on the pagination feature!
        //          Is it even worth distinguishing between different
        //          features? Could stick to keeping filters and sorting
        //          robust and hard-coded; keep pagination strategies
        //          variable and let consumers of this library pick and
        //          choose what type of pagination strategy they would like
        //          to support.
        //
        //          For the sake of this project, pagination links can be
        //          derived using returned data so this is not a critical
        //          issue.
        //
        // $document['links']['prev'] = null;
        // $document['links']['next'] = null;

        $data = reset($data);
        
        if ($amount & R::ONE) {
            $document['data'] = current($data) ?: null;
        } else {
            $document['data'] = [];

            // Note: To avoid duplication, resources are indexed by ID.
            //       Omit the index from the final result.
            foreach ($data as $resource) {
                $document['data'][] = $resource;
            }
        }
        // END(after resolve)

        // TODO:    The JSON:API spec does mention to return an error document
        //          with a top-level "error" namespace in the event of an error.
        //          Currently this is omitted.
        $response->getBody()->write(json_encode($document));

        return $response;
    }
}
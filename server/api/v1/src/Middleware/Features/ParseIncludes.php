<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ThePetPark\Library\Graph\Schema\ReferenceTable;
use ThePetPark\Library\Graph\Schema\Relationship as R;

use function reset;
use function current;
use function next;
use function explode;

/**
 * Add included data to the document.
 */
final class ParseIncludes
{
    private $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;    
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {

        /** @var array */
        $document = $request->getAttribute(Resolver::DOCUMENT);

        /** @var array */
        $data = $request->getAttribute(Resolver::DATA);

        /** @var array */
        $params = $request->getAttribute(Resolver::PARAMETERS);

        $rowCount = count($data);

        if ($rowCount === 0 || isset($params['include']) === false) {
            return $next($request, $response);
        }

        /** @var \ThePetPark\Library\Graph\Schema\ReferenceTable */
        $refs = $request->getAttribute(ReferenceTable::class);

        /** @var \Doctrine\DBAL\Query\QueryBuilder */
        $qb = $request->getAttribute(QueryBuilder::class);

        $base = $refs->getBaseRef();

        $type = $base->getSchema()->getType();

        $resolved = [$type => []];

        $refQueryMap = [];

        $nqueries = 0;
        
        $records = [];

        // There should only be one item in the data array.
        // Mark each record as resolved.
        foreach (current($data) as $record) {
            $resolved[$type][$record['id']] = true;
        }

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
            // Each related resource query is based on the previous query.
            $sub = clone $qb;
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
                    $relatedRef = $refs->resolve($relationship, $ref, $sub);
                }
                
                $refs->setParentRef($relatedRef, $ref, $sub);

                $ref = $relatedRef;
                $delim = '.';

                $refQueryMap[$ref->getRef()] = $nqueries;
            }

            if (isset($resolved[$ref->getSchema()->getType()]) === false) {
                $resolved[$ref->getSchema()->getType()] = [];
            }

            $records[$nqueries++] = $sub->execute()->fetchAll(FetchMode::ASSOCIATIVE);
        }

        foreach ($refs->getParentRefs() as $refID => $parent) {
            $data[$refID] = [];
            $ref = $refs->getRefById($refID);
            $schema = $ref->getSchema();
            $prefix = $ref . '_';

            foreach ($records[$refQueryMap[$ref->getRef()]] as $rec) {
                $resourceID = $rec[$prefix . 'id'];
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
                
                // Ignore duplicates. There may be many of them!
                if (isset($data[$refID][$resourceID]) === false) {
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
        reset($data);
        
        // Main data is the first element, skip it.
        $included = next($data);
        
        $document['included'] = [];
        
        // Note:    Some included resources may contain duplicate data.
        //          This is why the "resolved" map exists.
        // 
        // e.g.     Fetching article authors and article comment authors -- an
        //          article author can also be a comment author. Don't include
        //          the same person twice!      
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

        return $next(
            $request
                ->withAttribute(Resolver::DATA, $data)
                ->withAttribute(Resolver::DOCUMENT, $document),
            $response
        );
    }
}
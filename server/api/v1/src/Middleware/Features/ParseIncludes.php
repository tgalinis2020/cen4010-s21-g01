<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features;

use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ThePetPark\Schema\ReferenceTable;
use ThePetPark\Schema\Relationship as R;

use PDO;

use function reset;
use function explode;
use function count;

/**
 * Add related resources to the data collection.
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
        $data = $request->getAttribute(Resolver::DATA);

        /** @var array */
        $params = $request->getAttribute(Resolver::PARAMETERS);

        $rowCount = count($data);

        if ($rowCount === 0 || isset($params['include']) === false) {
            return $next($request, $response);
        }

        /** @var \ThePetPark\Schema\ReferenceTable */
        $refs = $request->getAttribute(ReferenceTable::class);

        /** @var \Doctrine\DBAL\Query\QueryBuilder */
        $qb = $request->getAttribute(QueryBuilder::class);

        $base = $refs->getBaseRef();

        $refQueryMap = [];

        $nqueries = 0;
        
        $records = [];

        // Reset fields but keep source table(s), conditions and base IDs.
        // Create a new query based on retrieved data. Keep the base
        // resource's ID since it will be needed for relationship
        // propagation.
        $qb->resetQueryParts(['select', 'orderBy'])
            ->setFirstResult(null)
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

                $relatedRef = $refs->resolve($token, $ref, $sub);
                
                // The set*Ref methods add the attributes of the target
                // resource to the query using the provided QueryBuilder
                // instance.
                $refs->setParentRef($relatedRef, $ref, $sub);

                $ref = $relatedRef;
                $delim = '.';

                $refQueryMap[$ref->getRef()] = $nqueries;
            }

            $records[$nqueries++] = $sub->execute()->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach ($refs->getParentRefs() as $refID => $parent) {
            $data[$refID] = [];
            $ref = $refs->get($refID);
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

        return $next(
            $request->withAttribute(Resolver::DATA, $data),
            $response
        );
    }
}
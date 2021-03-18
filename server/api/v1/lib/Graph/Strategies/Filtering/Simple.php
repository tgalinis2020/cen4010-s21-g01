<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Strategies\Filtering;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph\Graph;
use ThePetPark\Library\Graph\StrategyInterface;

/**
 * This filtering strategy only supports the equals operation.
 * 
 * E.g. Fetch articles that were posted on March 17th, 2021
 * 
 * GET /articles?filter[createdAt]=2021-03-17
 */
class Simple implements StrategyInterface
{
    public function apply(Graph $graph, QueryBuilder $qb, array $params): bool
    {
        $reftable = $graph->getReferenceTable();

        foreach (($params['filter'] ?? []) as $field => $value) {
            $ref = $reftable->getBaseRef();
            $resource = $graph->getByRef($ref);

            // TOOD: parse provided field. Fields can be attributes of
            // the resource or attributes of a resource from a resolved
            // relationship. Might have to add joins to apply the filter.
            // If this is the case, add the new reference to the ref map.
            $tokens = explode('.', $field);
            $attribute = array_pop($tokens);
            $delim = '';
            $token = '';

            foreach ($tokens as $r) {

                $token .= $delim . $r;

                $relatedRef = $reftable->newRef($token, $ref);
                $relationship = $resource->resolve($graph, $qb, $r, $ref, $relatedRef);
                $relatedResource = $relationship->getSchema();
                $reftable->setResource($relatedRef, $relatedResource);

                $ref = $relatedRef;
                $resource = $relatedResource;
                $delim = '.';

            }

            $qb->andWhere($qb->expr()->eq(
                $ref . '.' . $attribute,
                $qb->createNamedParameter($value)
            ));
        }

        return true;
    }
}
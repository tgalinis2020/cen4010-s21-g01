<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Strategies\Filtering;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ThePetPark\Library\Graph\Graph;
use ThePetPark\Library\Graph\ReferenceTable;
use ThePetPark\Library\Graph\StrategyInterface;

/**
 * This filtering strategy adds granular filters, such as <, <=, >, and >=.
 * 
 * E.g. Fetch articles that were posted before March 17th, 2021
 * 
 * GET /articles?filter[createdAt][lt]=2021-03-17
 */
class Granular implements StrategyInterface
{
    public function apply(
        Graph $graph,
        ReferenceTable $reftable,
        QueryBuilder $qb,
        CompositeExpression $conditions,
        array $params
    ): bool {
        
        foreach ($params['filter'] as $field => $filters) {
            $ref = $reftable->getBaseRef();
            $resource = $graph->get($reftable->getResourceType($ref));

            // TOOD: parse provided field. Fields can be attributes of
            // the resource or attributes of a resource from a resolved
            // relationship. Might have to add joins to apply the filter.
            // If this is the case, add the new reference to the ref map.
            $tokens = explode('.', $field);
            $attribute = array_pop($tokens);

            foreach ($tokens as $relationship) {

            }
        }

        return true;

    }
}
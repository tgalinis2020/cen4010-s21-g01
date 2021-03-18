<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Strategies\Filtering;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use ThePetPark\Library\Graph\Graph;
use ThePetPark\Library\Graph\StrategyInterface;

/**
 * This filtering strategy adds granular filters, such as <, <=, >, and >=.
 * 
 * E.g. Fetch articles that were posted before March 17th, 2021
 * 
 * GET /articles?filter[createdAt:lt]=2021-03-17
 */
class Granular implements StrategyInterface
{
    const SUPPORTED_FILTERS = [
        'eq' => ExpressionBuilder::EQ,
        'ne' => ExpressionBuilder::NEQ,
        'lt' => ExpressionBuilder::LT,
        'le' => ExpressionBuilder::LTE,
        'gt' => ExpressionBuilder::GT,
        'ge' => ExpressionBuilder::GTE,
    ];

    public function apply(Graph $graph, QueryBuilder $qb, array $params): bool
    {
        $reftable = $graph->getReferenceTable();

        foreach (($params['filter'] ?? []) as $fieldAndFilter => $value) {
            $ref = $reftable->getBaseRef();
            $resource = $graph->getByRef($ref);
            $tokens = explode(':', $fieldAndFilter);

            if (count($tokens) > 2) {
                return false; // Malformed filter, stop here
            }

            list($field, $filter) = array_replace([null, 'eq'], $tokens);

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

            if (isset(self::SUPPORTED_FILTERS[$filter])) {
                $qb->andWhere($qb->expr()->comparison(
                    $ref . '.' . $attribute,
                    self::SUPPORTED_FILTERS[$filter],
                    $qb->createNamedParameter($value)
                ));
            }
        }

        return true;
    }
}
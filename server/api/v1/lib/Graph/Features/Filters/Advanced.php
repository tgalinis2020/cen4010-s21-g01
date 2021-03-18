<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Features\Filters;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use ThePetPark\Library\Graph;

use function array_replace;

/**
 * This filtering strategy adds granular filters, such as <, <=, >, and >=.
 * 
 * E.g. Fetch articles that were posted before March 17th, 2021
 * 
 * GET /articles?filter[createdAt:lt]=2021-03-17
 */
class Advanced implements Graph\FeatureInterface
{
    const SUPPORTED_FILTERS = [
        'eq' => ExpressionBuilder::EQ,
        'ne' => ExpressionBuilder::NEQ,
        'lt' => ExpressionBuilder::LT,
        'le' => ExpressionBuilder::LTE,
        'gt' => ExpressionBuilder::GT,
        'ge' => ExpressionBuilder::GTE,
    ];

    public function check(array $params): bool
    {
        return isset($params['filter']);
    }

    public function apply(Graph\App $graph, QueryBuilder $qb, array $params): bool
    {
        foreach ($params['filter'] as $fieldAndFilter => $value) {
            $ref = $graph->getBaseRef();
            $schema = $graph->getSchemaByRef($ref);
            $tokens = explode(':', $fieldAndFilter);

            if (count($tokens) > 2) {
                return false; // Malformed filter, stop here
            }

            list($field, $filter) = array_replace([null, 'eq'], $tokens);

            if (isset(self::SUPPORTED_FILTERS[$filter]) === false) {
                return false;
            }

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

                if ($graph->hasRefForToken($token)) {

                    $ref = $graph->getRefByToken($token);
                    $schema = $graph->getSchemaByRef($ref);
                    
                } else {

                    $relationship = $schema->resolve($graph, $qb, $ref, $r);
                    $ref = $relationship->getRef();
                    $schema = $relationship->getSchema();

                }

                $delim = '.';
            }

            if ($attribute === 'id') {

                $field = $schema->getId();

            } elseif ($schema->hasAttribute($attribute)) {

                $field = $schema->getImplAttribute($attribute);

            } elseif ($schema->hasRelationship($attribute)) {

                $relationship = $schema->getRelationship($attribute);
                $ref = $relationship->getRef();
                $schema = $relationship->getSchema();
                $field = $schema->getId();

            } else {

                return false; // Malformed expression, attribute does not exist
            
            }
            
            $qb->andWhere($qb->expr()->comparison(
                $ref . '.' . $attribute,
                self::SUPPORTED_FILTERS[$filter],
                $qb->createNamedParameter($value)
            ));
        }

        return true;
    }
}
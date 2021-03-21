<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Features\Filters;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use ThePetPark\Library\Graph;

use function array_replace;
use function array_pop;
use function explode;

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

    public function clean(array &$params)
    {
        unset($params['filter']);
    }

    public function apply(Graph\App $graph, array $params): bool
    {
        $qb = $graph->getQueryBuilder();

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

                    list($schema, $ref, $mask) = $graph->resolve($r, $ref);

                }

                $delim = '.';
            }

            if ($attribute === 'id') {

                $attribute = $schema->getId();

            } elseif ($schema->hasAttribute($attribute)) {

                $attribute = $schema->getImplAttribute($attribute);

            } elseif ($schema->hasRelationship($attribute)) {

                $token = $delim . $attribute;
                
                if ($graph->hasRefForToken($token)) {

                    $ref = $graph->getRefByToken($token);
                    $schema = $graph->getSchemaByRef($ref);
                    
                } else {

                    list($schema, $ref, $mask) = $graph->resolve($r, $ref);

                }

                $attribute = $schema->getId();

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
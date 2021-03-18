<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Features\Filters;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph;

use function array_pop;

/**
 * This filtering strategy only supports the equals operation.
 * 
 * E.g. Fetch articles that were posted on March 17th, 2021
 * 
 * GET /articles?filter[createdAt]=2021-03-17
 */
class Simple implements Graph\FeatureInterface
{
    public function check(array $params): bool
    {
        return isset($params['filter']);
    }

    public function apply(Graph\App $graph, QueryBuilder $qb, array $params): bool
    {
        foreach ($params['filter'] as $field => $value) {
            $ref = $graph->getBaseRef();
            $schema = $graph->getSchemaByRef($ref);

            // TOOD: parse provided field. Fields can be attributes of
            // the resource or attributes of a resource from a resolved
            // relationship. Might have to add joins to apply the filter.
            // If this is the case, add the new reference to the ref map.
            $tokens = explode('.', $field);
            $field = array_pop($tokens);
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

            if ($field === 'id') {

                $field = $schema->getId();

            } elseif ($schema->hasAttribute($field)) {

                $field = $schema->getImplAttribute($field);

            } elseif ($schema->hasRelationship($field)) {

                $relationship = $schema->getRelationship($field);
                $ref = $relationship->getRef();
                $schema = $relationship->getSchema();
                $field = $schema->getId();

            } else {

                return false; // Malformed expression, attribute does not exist
            
            }

            $qb->andWhere($qb->expr()->eq(
                $ref . '.' . $field,
                $qb->createNamedParameter($value)
            ));
        }

        return true;
    }
}
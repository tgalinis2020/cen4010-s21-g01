<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Features\Filters;

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

    public function clean(array &$params)
    {
        unset($params['filter']);
    }

    public function apply(Graph\App $graph, array $params): bool
    {
        $qb = $graph->getQueryBuilder();

        foreach ($params['filter'] as $field => $value) {
            $ref = $graph->getBaseRef();
            $tokens = explode('.', $field);
            $attribute = array_pop($tokens);
            $delim = '';
            $token = '';

            foreach ($tokens as $r) {
                $token .= $delim . $r;

                $ref = $graph->hasRefForToken($token)
                    ? $graph->getRefByToken($token)
                    : $graph->resolve($r, $ref);

                $delim = '.';
            }

            if ($attribute === 'id') {

                $attribute = $ref->getSchema()->getId();

            } elseif ($ref->getSchema()->hasAttribute($attribute)) {

                $attribute = $ref->getSchema()->getImplAttribute($attribute);

            } elseif ($ref->getSchema()->hasRelationship($attribute)) {

                $token = $delim . $attribute;
                
                $ref = $graph->hasRefForToken($token)
                    ? $graph->getRefByToken($token)
                    : $graph->resolve($r, $ref);

                $attribute = $ref->getSchema()->getId();

            } else {

                return false; // Malformed expression, attribute does not exist
            
            }
            
            $qb->andWhere($qb->expr()->eq(
                $ref . '.' . $attribute,
                $qb->createNamedParameter($value)
            ));
        }

        return true;
    }
}

<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

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
class Filters implements Graph\FeatureInterface
{
    use Graph\Drivers\Doctrine\FeatureTrait;

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

    public function apply(array $params, ReferenceTable $refs): bool
    {
        $qb = $this->driver->getQueryBuilder();
    
        foreach ($params['filter'] as $fieldAndFilter => $value) {
            $ref = $refs->getBaseRef();
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

                $ref = $refs->has($token)
                    ? $refs->get($token)
                    : $refs->resolve($r, $ref, $this->driver);

                $delim = '.';
            }

            if ($attribute === 'id') {

                $attribute = $ref->getSchema()->getId();

            } elseif ($ref->getSchema()->hasAttribute($attribute)) {

                $attribute = $ref->getSchema()->getImplAttribute($attribute);

            } elseif ($ref->getSchema()->hasRelationship($attribute)) {

                $token = $delim . $attribute;
                
                $ref = $refs->has($token)
                    ? $refs->get($token)
                    : $refs->resolve($r, $ref, $this->driver);

                $attribute = $ref->getSchema()->getId();

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
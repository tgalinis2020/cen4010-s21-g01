<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

use function is_array;
use function in_array;
use function array_pop;
use function explode;

/**
 * This filtering strategy adds granular filters, such as <, <=, >, and >=.
 * 
 * E.g. Fetch articles that were posted before March 17th, 2021
 * 
 * GET /articles?filter[createdAt][lt]=2021-03-17
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
        'lk' => 'LIKE',
        'nl' => 'NOT LIKE',
        'in' => 'IN',
        'ni' => 'NOT IN',
    ];

    public function apply(array $params, ReferenceTable $refs): bool
    {
        if (isset($params['filter']) === false) {
            return false;
        }

        $qb = $this->driver->getQueryBuilder();
    
        foreach ($params['filter'] as $fullyQualifiedField => $filterAndValue) {
            $ref = $refs->getBaseRef();

            // If there is no filter explicitly given, default to "eq"
            if (is_array($filterAndValue) === false) {
                $filterAndValue = ['eq' => $filterAndValue];
            }

            $tokens = explode('.', $fullyQualifiedField);
            $field = array_pop($tokens);
            $delim = '';
            $token = '';

            foreach ($tokens as $relationship) {
                $token .= $delim . $relationship;

                // TODO:    Filters are evaulated before resolving any resources.
                //          Might not even be worth checking if a reference
                //          exists for the provided token.
                if ($refs->has($token)) {
                    $ref = $refs->get($token);
                } else {
                    $related = $refs->resolve($field, $ref);
                    $this->driver->resolve($related, $ref);
                    $ref = $related;
                }

                $ref = $related;
                $delim = '.';
            }

            /** @var string $filter */
            foreach ($filterAndValue as $filter => $value) {
                if (isset(self::SUPPORTED_FILTERS[$filter])) {
                    if ($field === 'id') {

                        $field = $ref->getSchema()->getId();

                    } elseif ($ref->getSchema()->hasAttribute($field)) {

                        $field = $ref->getSchema()->getImplAttribute($field);

                    } elseif ($ref->getSchema()->hasRelationship($field)) {

                        $token = $delim . $field;
                        
                        if ($refs->has($token)) {
                            $ref = $refs->get($token);
                        } else {
                            $related = $refs->resolve($field, $ref);
                            $this->driver->resolve($related, $ref);
                            $ref = $related;
                        }

                        $field = $ref->getSchema()->getId();

                    } else {

                        $field = null;

                    }

                    if ($field !== null) {
                        // TODO:    This is kind of ugly :(
                        //          The IN and NOT IN operataions are unique:
                        //          they accept a variable amount of arguments.
                        if (in_array($filter, ['in', 'ni'])) {
                            $vals = [];

                            foreach (explode(',', $value) as $val) {
                                $vals[] = $qb->createNamedParameter($val);
                            }

                            $value = '(' . implode(', ', $vals) . ')';
                        } else {
                            $value = $qb->createNamedParameter($value);
                        }

                        $qb->andWhere($qb->expr()->comparison(
                            $ref . '.' . $field,
                            self::SUPPORTED_FILTERS[$filter],
                            $value
                        ));
                    }
                }
            }
        }

        return true;
    }
}
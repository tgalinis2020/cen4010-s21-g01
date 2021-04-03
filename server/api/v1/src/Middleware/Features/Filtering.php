<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ThePetPark\Schema\ReferenceTable;

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
final class Filtering
{
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

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        
        $params = $request->getAttribute(Resolver::PARAMETERS);

        if (is_array($params['filter'] ?? '') === false) {
            return $next($request, $response);
        }

        $qb = $request->getAttribute(QueryBuilder::class);
        $refs = $request->getAttribute(ReferenceTable::class);

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

                $ref = $refs->resolve($token, $ref, $qb);

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

                        $token .= $delim . $field;

                        $ref = $refs->resolve($token, $ref, $qb);

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
                                $vals[] = $this->qb->createNamedParameter($val);
                            }

                            $value = '(' . implode(',', $vals) . ')';
                        } else {
                            $value = $this->qb->createNamedParameter($value);
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

        return $next($request, $response);
    }
}
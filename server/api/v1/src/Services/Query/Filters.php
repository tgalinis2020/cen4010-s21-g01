<?php

declare(strict_types=1);

namespace ThePetPark\Services\Query;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

use function explode;
use function call_user_func;

/**
 * Maps any valid filters in the request with a corresponding ExpressionBuilder
 * function.
 * 
 * TODO: maybe support these types of filters:
 * 
 * /foo?filter[fooattr][gt]=5
 * /foo?filter[foorelationship.attr][in]=1,2,3,4,5
 * /foo?filter[foo.fooattr]=5 // synonymous with [eq]=5
 */
class Filters
{
    // Status codes that can be returned by QueryFilters::apply.
    const SUCCESS       = 0;
    const EINVALIDFIELD = 1;
    const EINVALIDEXPR  = 2;

    // API filter to ExpressionBuilder function mappings.
    // There are subtle differences.
    const FILTERS = [
        'eq' => 'eq',
        'gt' => 'gt',
        'ge' => 'gte',
        'lt' => 'lt',
        'le' => 'lte',
        'in' => 'in,'
    ];

    /** @var array */
    private $fieldMap;

    /** @var \Doctrine\DBAL\Query\QueryBuilder */
    private $qb;

    /** @var \Doctrine\DBAL\Query\Expression\CompositeExpression */
    private $conditions;

    public function __construct(
        QueryBuilder $qb,
        CompositeExpression $conditions,
        array $fieldMap
    ) {
        $this->qb = $qb;
        $this->conditions = $conditions;
        $this->fieldMap = $fieldMap;
    }

    /**
     * Attempts to apply the provided filters to the query builder.
     * If it doesn't for any reason, it returns a nonzero error code.
     */
    public function apply(array $filters): int
    {
        foreach ($filters as $field => $rvalue) {
            $tokens = explode(' ', $rvalue);

            switch (count($tokens)) {
            case 1:
                $tokens = ['eq', $rvalue];
            case 2:
                list($op, $value) = $tokens;

                if (!isset($this->expressions[$op])) {
                    return self::EINVALIDEXPR;
                }

                if (!isset($this->fieldMap[$field])) {
                    return self::EINVALIDFIELD;
                }

                // This silly looking block of code calls the filter's
                // corresponding ExpressionBuilder method.
                $this->conditions->add(call_user_func(
                    [$this->qb->expr(), self::FILTERS[$op]],
                    $this->fieldMap[$field],
                    $this->qb->createNamedParameter($value)
                ));
            }
        }

        return self::SUCCESS;
    }
}
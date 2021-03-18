<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

/**
 * Strategies allow different ways to apply sorting, filtering and pagination
 * to a request. Breaks a big actions into smaller, replaceable actions.
 */
interface StrategyInterface
{
    /**
     * Apply a transformation to the query.
     * 
     * @return bool True if transformation was applied successfully, false otherwise.
     */
    public function apply(Graph $graph, QueryBuilder $qb, array $params): bool;
}
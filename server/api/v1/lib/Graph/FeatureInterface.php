<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Features allow different ways to apply sorting, filtering and pagination
 * to a request. Breaks big actions into smaller, interchangeable components.
 */
interface FeatureInterface
{
    /**
     * Checks the provided parameters to see if the feature is applicable.
     */
    public function check(array $parameters): bool;

    /**
     * If necessary, unset any parameters that could be used by conflicting
     * filters.
     */
    public function clean(array &$parameters);
    
    /**
     * Apply a transformation to the query.
     * 
     * @return bool True if transformation was applied successfully, false otherwise.
     */
    public function apply(App $graph, QueryBuilder $qb, array $parameters): bool;
}
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
     * Since features are evaluated from first to last, features with more
     * specific requirements should be defined first.
     */
    public function check(array $parameters): bool;

    /**
     * If the feature executed, it should unset any parameters it parsed
     * to avoid executing similar features.
     * 
     * For instance, problems could occur if both the offset/limit and cursor/size
     * pagination strategies were supported and the "page" query parameter was
     * not unset.
     */
    public function clean(array &$parameters);
    
    /**
     * Apply a transformation to the query.
     * 
     * @return bool True if transformation was applied successfully, false otherwise.
     */
    public function apply(App $graph, array $parameters): bool;
}
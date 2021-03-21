<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use ThePetPark\Library\Graph\Schema\ReferenceTable;

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
     * Apply a transformation to the query.
     * 
     * @return bool True if transformation was applied successfully, false otherwise.
     */
    public function apply(array $parameters, ReferenceTable $refs): bool;
}
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
     * Apply a transformation to the query.
     * 
     * @return bool True if transformation was applied successfully, false otherwise.
     */
    public function apply(array $parameters, ReferenceTable $refs): bool;
}
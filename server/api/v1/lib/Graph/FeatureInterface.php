<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use ThePetPark\Library\Graph\Schema;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

/**
 * Features allow different ways to apply sorting, filtering and pagination
 * to a request. Breaks big actions into smaller, interchangeable components.
 */
interface FeatureInterface
{
    /**
     * @return string The key in the parameters array.
     */
    public function provides(): string;

    /**
     * Apply a transformation to the query.
     * 
     * @return bool True if transformation was applied successfully, false otherwise.
     */
    public function apply(array $parameters, Schema\Container $schemas, ReferenceTable $refs): bool;
}
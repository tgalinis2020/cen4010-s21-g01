<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use ThePetPark\Library\Graph\Schema\ReferenceTable;

abstract class AbstractDriver
{
    /** @var \ThePetPark\Library\Graph\FeatureInterface[] */
    protected $features;

    /** @return \ThePetPark\Library\Graph\FeatureInterface[] */
    public function getFeatures(array $params): array
    {
        $features = [];

        foreach ($this->features as $feat) {
            if (isset($params[$feat->provides()])) {
                $features[] = $feat;
            }
        }

        return $features;
    }
    
    public function apply(array $params, Schema\Container $schemas, ReferenceTable $refs)
    {
        foreach ($this->features as $feat) {
            if (isset($params[$feat->provides()])) {
                $feat->apply($params, $schemas, $refs);
            }
        }
    }

    /**
     * Set the provided reference as the source of the query.
     */
    abstract public function setSource(Schema\Reference $source);

    /**
     * Select a specific resource from a the resource collection pointed to by
     * the source reference.
     */
    abstract public function select(Schema\Reference $source, string $resourceID);

    /**
     * Prepares a query using the schema's ID and attributes. 
     */
    abstract public function prepare(Schema\Reference $source);


    /**
     * Performs the necessary tasks to link the source reference with the given
     * relationship reference.
     */
    abstract public function resolve(Schema\Relationship $relationship, Schema\Reference $source);

    /**
     * Constrains the source's included data.
     */
    abstract public function setRange(Schema\Reference $source, string $from, string $to);

    /**
     * Execute the query and return raw data in the form of an array of records.
     * Each record MUST be an associative array having the following shape
     * for each field:
     * 
     * {reference enum}_{source schema attribute}
     */
    abstract public function execute(): array;

    /**
     * If fetching included data, reset the query such that it doesn't select
     * the main data again. Reset the fields to select but keep the applied
     * conditions.
     */
    abstract public function reset(Schema\Reference $source);
}
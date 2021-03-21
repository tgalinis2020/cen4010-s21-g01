<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Schema;

use Psr\Container\ContainerInterface;
use ThePetPark\Library\Graph\AbstractDriver;
use ThePetPark\Library\Graph\Schema;

use function substr;
use function strrchr;

class ReferenceTable implements ContainerInterface
{
    /**
     * Reference number of most recently enumerated value.
     * 
     * @var int
     */
    protected $refcount = 0;

    /**
     * Token-to-reference map.
     * 
     * @var \ThePetPark\Library\Graph\Schema\Relationship[]
     */
    protected $references = [];

    /**
     * Reference-to-parent reference map. Required for propagating relationship
     * data to the parent resource.
     * 
     * @var Schema\Reference[]
     */
    protected $parentRefs = [];

    /**
     * Source of all data. Must be a Schema reference, not a relationship.
     * 
     * @var \ThePetPark\Library\Graph\Schema\Reference
     */
    protected $sourceRef;

    /**
     * The source of data to select from.
     * 
     * Normally it is the source reference but it will change if requesting a
     * derived resource.
     * 
     * @var \ThePetPark\Library\Graph\Schema\Reference
     */
    protected $baseRef;

    /** @var \ThePetPark\Library\Graph\Schema\Container */
    protected $schemas;

    /** @param \ThePetPark\Library\Graph\Schema\Container $schemas */
    public function __construct(Schema\Container $schemas)
    {
        $this->schemas = $schemas;
    }

    /**
     * Initialize the reference table using provided token.
     */
    public function init(string $type, AbstractDriver $driver)
    {
        $ref = new Schema\Reference($this->refcount++, $this->schemas->get($type));
        
        $this->sourceRef = $this->baseRef = $ref;

        $driver->init($ref);

        return $ref;
    }

    public function get(string $token): Schema\Reference
    {
        return $this->references[$token];
    }

    public function has(string $token): bool
    {
        return isset($this->references[$token]);
    }

    /**
     * Adds related schema to the driver's query and generates a new unique
     * reference for the relationship.
     */
    public function resolve(
        string $token,
        Schema\Reference $source,
        AbstractDriver $driver
    ): Schema\Relationship {
        $id = $this->refcount++;

        $name = substr(strrchr($token, '.') ?: '', 1) ?: $token;
        
        list($mask, $type, $link) = $source->getSchema()->getRelationship($name);

        $related = new Schema\Relationship(
            $id,
            $name,
            $link,
            $mask,
            $this->schemas->get($type)
        );

        $this->references[$token] = $related;
        $this->parentRefs[$id] = $source;

        $driver->resolve($source, $related);

        return $related;
    }

    /**
     * If selecting data derived from a resource's relationship, the
     * reference to the relationship should be promoted to the base reference.
     * 
     * Unset the relationship's row in parentRefs to avoid propagating
     * relationship data to a resource that isn't selected.
     */
    public function setBaseRef(Schema\Relationship $ref)
    {
        $this->baseRef = $ref;
    }

    public function setParentRef(Schema\Relationship $ref, Schema\Reference $parent)
    {
        $this->parentRefs[$ref->getRef()] = $parent;
    }

    public function getBaseRef(): Schema\Reference
    {
        return $this->baseRef;
    }

    public function scan(AbstractDriver $driver): array
    {
        $prefix = $this->baseRef . '_';
        $data = [];

        foreach ($driver->fetchAll() as $record) {
            $resourceID = $record[$prefix . 'id'];
            $data[$resourceID] = [];

            foreach ($this->baseRef->getSchema()->getAttributes() as $attr) {
                $data[$resourceID][$attr] = $record[$prefix . $attr];
            }
        }

        return $data;
    }

    public function scanIncluded(AbstractDriver $driver): array
    {
        $data = [];
        $relationships = [];

        // Initialize data and relationship collections using the list of
        // child-to-parent references.
        foreach ($this->parentRefs as $childRefID => $parentRef) {
            $data[$childRefID] = [];

            if (isset($relationships[$parentRef->getRef()]) === false) {
                $relationships[$parentRef->getRef()] = [];
            }

            $relationships[$parentRef->getRef()][$childRefID] = [];
        }
    
        foreach ($driver->fetchAll() as $record) {
            foreach ($this->parentRefs as $refID => $parentRef) {
                $ref = $this->references[$refID];
                $prefix = $ref->getRef() . '_';
                $resourceID = $record[$prefix . 'id'];

                // Ignore duplicates. There may be many of them!
                if (isset($data[$refID][$resourceID]) === false) {
                    $data[$refID][$resourceID] = [];
                    $relationships[$parentRef->getRef()][$refID][] = $resourceID;

                    foreach ($ref->getSchema()->getAttributes() as $attr) {
                        $data[$refID][$attr] = $record[$prefix . $attr];
                    }
                }
            }
        }

        return [$data, $relationships];
    }
}
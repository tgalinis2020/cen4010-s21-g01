<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Schema;

use Psr\Container\ContainerInterface;
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
     * Token-to-reference ID map.
     * 
     * @var array
     */
    protected $map = [];

    /**
     * Reference ID-to-reference map.
     * Root reference must be a Schema\Reference.
     * All other references are of type Schema\Relationship.
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
     * The source of data to select from.
     * 
     * Normally it is the root reference ID but it will change if requesting a
     * derived resource.
     * 
     * @var int
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
    public function init(string $type)
    {
        $this->baseRef = $this->refcount;
        
        $ref = new Schema\Reference($this->baseRef, $this->schemas->get($type));
        
        $this->references[$this->refcount++] = $ref;

        return $ref;
    }

    /**
     * Note: although the root Schema\Reference is present in the references
     * collection, it is not present in the token-to-reference map.
     * 
     * Therefore, this function can only return relationships.
     */
    public function get(string $token): Schema\Relationship
    {
        return $this->references[$this->map[$token]];
    }

    public function has(string $token): bool
    {
        return isset($this->map[$token]);
    }

    /**
     * Adds related schema to the driver's query and generates a new unique
     * reference for the relationship.
     */
    public function resolve(string $token, Schema\Reference $source): Schema\Relationship
    {
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

        $this->map[$token] = $id;
        $this->references[$id] = $related;

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
        $this->baseRef = $ref->getRef();
    }

    public function setParentRef(Schema\Relationship $ref, Schema\Reference $parent)
    {
        $this->parentRefs[$ref->getRef()] = $parent;
    }

    public function getBaseRef(): Schema\Reference
    {
        return $this->references[$this->baseRef];
    }

    /** @return \ThePetPark\Library\Graph\Schema\Reference[] */
    public function getParentRefs(): array
    {
        return $this->parentRefs;
    }

    public function getRefById(int $id): Schema\Relationship
    {
        return $this->references[$id];
    }
}
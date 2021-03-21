<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Schema;

use Psr\Container\ContainerInterface;
use ThePetPark\Library\Graph\Schema;

use function substr;
use function strrchr;

class ReferenceTable implements ContainerInterface
{
    const REF_PREFIX = 't';

    /**
     * Reference number of most recently enumerated value.
     * 
     * @var int
     */
    protected $refcount = 0;

    /**
     * Token-to-reference map.
     * 
     * @var Schema\Reference[]
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
     * Maps a reference to the type of resource its pointing to.
     * 
     * @var string[]
     */
    //protected $resourceTypes = [];

    /**
     * The source of everything. Its value will be used as a prefix for all
     * references added to this table.
     * 
     * @var string
     */
    protected $tokenPrefix;

    /**
     * The source of data.
     * 
     * @var Schema\Reference
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
        $ref = new Schema\Reference($this->refcount++, $this->schemas->get($type));
        
        $this->tokenPrefix = $type;
        $this->references[$type] = $this->baseRef = $ref;

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

    // BEGIN(Reftable methods)
    /**
     * Creating new references results in a new reference ID.
     */
    public function createRef(string $token, Schema\Reference $parent): Schema\Relationship
    {
        $id = $this->refcount++;
        $token = $this->tokenPrefix . '.' . $token;

        $name = substr(strrchr($token, '.'), 1) ?: '';
        list($mask, $resourceType, $link) = $parent->getSchema()
            ->getRelationship($name);

        $ref = new Schema\Relationship(
            $id,
            $name,
            $link,
            $mask,
            $this->schemas->get($resourceType)
        );

        $this->references[$token] = $ref;
        $this->parentRefs[$id] = $parent;
        //$this->resourceTypes[$id] = $resourceType;
        
        // Keep track of child-to-parent relationships; required for propagating
        // data to the parent resource's rel map.

        // If a parent is provided, a relationship name *should* be
        // available. Relationship tokens are delimited with a period.
        //$this->relationshipNames[$id] = $relationshipName;

        /*$this->raw[$id] = [];
        $this->data[$id] = [];
        $this->relationships[$id] = [];*/

        return $ref;
    }

    public function getResourceType(string $ref): string
    {
        return $this->resourceTypes[$ref];
    }

    public function getRelationshipName(string $ref): string
    {
        return $this->relationshipNames[$ref];
    }

    public function hasRefForToken(string $token): bool
    {
        return isset($this->references[$token]);
    }

    public function getRefByToken(string $token): Schema\Reference
    {
        return $this->references[$token];
    }

    public function getSchemaById(string $id)
    {
        return $this->references[$id]->getSchema()->getType();
    }

    /**
     * If selecting data derived from a resource's relationship, the
     * reference to the relationship should be promoted to the base reference.
     * 
     * Unset the relationship's row in parentRefs to avoid propegating
     * relationship data to a resource that isn't selected.
     */
    public function setBaseRef(Schema\Relationship $ref)
    {
        $this->baseRef = $ref;
        $this->tokenPrefix .= '.' . $ref->getName();

        unset($this->parentRefs[$ref]);
    }

    public function getBaseRef(): Schema\Reference
    {
        return $this->baseRef;
    }

    public function scan(array $raw)
    {
        $ref = $this->baseRef;
        $refID = $ref->getRef();
        $prefix = $refID. '_';
        $data = [];

        foreach ($raw as $record) {
            $resourceID = $record[$prefix . 'id'];

            // Ignore duplicates. There may be many of them!
            if (isset($data[$refID][$resourceID]) === false) {
                $data[$refID][$resourceID] = [];

                foreach ($ref->getSchema()->getAttributes() as $attr) {
                    $data[$refID][$attr] = $record[$prefix . $attr];
                }
            }
        }

        return $data;
    }

    public function scanIncluded(array $raw)
    {
        $data = [];
        $relationships = [];

        // Initialize data and relationship collections using the list of
        // child-to-parent references.
        foreach ($this->parentRefs as $refID => $parentRef) {
            $data[$refID] = [];

            if (isset($relationships[$parentRef]) === false) {
                $relationships[$parentRef] = [];
            }

            $relationships[$parentRef][$refID] = [];
        }
    
        foreach ($raw as $record) {
            foreach ($this->parentRefs as $refID => $parentRef) {
                $ref = $this->references[$refID];
                $prefix = $ref->getRef() . '_';
                $resourceID = $record[$prefix . 'id'];

                // Ignore duplicates. There may be many of them!
                if (isset($data[$refID][$resourceID]) === false) {
                    $data[$refID][$resourceID] = [];
                    $relationships[$parentRef][$refID][] = $resourceID;

                    foreach ($ref->getSchema()->getAttributes() as $attr) {
                        $data[$refID][$attr] = $record[$prefix . $attr];
                    }
                }
            }
        }

        return [$data, $relationships];
    }
}
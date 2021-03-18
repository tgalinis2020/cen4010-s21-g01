<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use function substr;
use function strrchr;

class ReferenceTable
{
    const PREFIX = 't';

    /**
     * Reference number of most recently enumerated value.
     * 
     * @var int
     */
    protected $ref = 0;

    /**
     * Token-to-reference map.
     * 
     * @var array
     */
    protected $references = [];

    /**
     * Reference-to-relationship name map. A name in this array corresponds to
     * the name of the relationship in the parent of index reference.
     * 
     * @var array
     */
    protected $relationshipNames = [];

    /**
     * Reference-to-parent reference map. Required for propagating relationship
     * data to the parent resource.
     * 
     * @var array
     */
    protected $parentRefs = [];

    /**
     * Maps a reference to the type of resource its pointing to.
     * 
     * @var array
     */
    protected $resourceTypes = [];

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
     * @var string
     */
    protected $baseRef;

    /**
     * Initialize the reference table using provided token.
     */
    public function __construct(string $resourceType)
    {
        $ref = $this->getLatestRef();

        $this->baseRef              = $ref;
        $this->tokenPrefix          = $resourceType;
        $this->resourceTypes[$ref]  = $resourceType;
        $this->raw[$ref]            = [];
        $this->data[$ref]           = [];
        $this->relationships[$ref]  = [];
    }

    public function newRef(string $token, string $parentRef)
    {
        $ref = self::PREFIX . (++$this->ref);
        $token = $this->tokenPrefix . '.' . $token;

        $this->references[$token] = $ref;
        
        // Keep track of child-to-parent relationships; required for propagating
        // data to the parent resource's rel map.
        $this->parentRefs[$ref] = $parentRef;

        // If a parent is provided, a relationship name *should* be
        // available. Relationship tokens are delimited with a period.
        $this->relationshipNames[$ref] = substr(strrchr($token, '.'), 1) ?: '';

        $this->raw[$ref]            = [];
        $this->data[$ref]           = [];
        $this->relationships[$ref]  = [];

        return $ref;
    }

    /**
     * References are required to enumerate a token. Using the reference,
     * a resource can be resolved.
     * 
     * This function must be called immediately after a resource has been
     * resolved.
     */
    public function setResource(string $ref, Schema $resource)
    {
        $this->resourceTypes[$ref] = $resource->getType();
    }

    public function getRelationshipName(string $ref): string
    {
        return $this->relationshipNames[$ref];
    }

    public function hasRefForToken(string $token): bool
    {
        return isset($this->references[$token]);
    }

    public function getRefByToken(string $token): string
    {
        return $this->references[$token];
    }

    /**
     * Promotes a relationship to the base reference.
     */
    public function pushRef(string $ref)
    {
        $this->baseRef = $ref;
        $this->tokenPrefix .= '.' . $this->relationshipNames[$ref];
    }

    public function getBaseRef(): string
    {
        return $this->baseRef;
    }

    public function getLatestRef(): string
    {
        return self::PREFIX . $this->ref;
    }

    public function getRootRef(): string
    {
        return self::PREFIX . '0';
    }

    public function getResourceType(string $ref): string
    {
        return $this->resourceTypes[$ref];
    }
}
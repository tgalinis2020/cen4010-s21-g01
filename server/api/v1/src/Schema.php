<?php

declare(strict_types=1);

namespace ThePetPark;

/**
 * An abstract representation of the back-end data model.
 * In order to create queries, schemas and the data model need to be one-to-one;
 * that is, very little underlying abstractions. Makes use of Doctrine's
 * Database Abstraction Layer for building queries.
 * 
 * Ultimately schemas aid in selecting resources; in other words, handling
 * GET requests. Ultimately it's the consumer's responsibility to parse the
 * request for data and return a response.
 * 
 * TODO: consider creating methods to ease parsing the request body for a
 * JSONAPI document object!
 */
class Schema
{
    const TYPE_NAME = 0;
    const TYPE_SRC  = 1;

    const ATTR_NAME = 0;
    const ATTR_SRC  = 1;
    const ATTR_DEFAULT_VALE = 2;

    /**
     * Back-end alias to resource ID. Defaults to "id".
     * 
     * @var string
     */
    protected $id;

    /**
     * Type of resource.
     * 
     * Signatures:
     * [resourcetype: string, backend_alias: string]
     * 
     * @var array
     */
    protected $type;

    /**
     * Resource attributes.
     * 
     * Attribute signature:
     * attr_name: string => [attr_name: string, backend_alias: string]
     * 
     * If alias is not provided, it is assumed that alias == attr_name.
     * 
     * TODO:    To support more advanced features (such as default values when
     *          creating resources), attributes need more information.
     *          Might be worth making it into a class, like Relationships.
     */
    protected $attributes;

    /**
     * Sparse fields.
     * 
     * @var string[]
     */
    protected $fields = [];

    /**
     * Resource relationship map. Used for combining related resources.
     * The resolution masks dictates whether or not the link comes from
     * this resource, the related resource, or a chain of relationships.
     * 
     * Relationship signature:
     * name: string => [mask: int, relatedType: string, link: string|array]
     */
    protected $relationships;

    /**
     * Far from a proper access control solution, but denoting what resource
     * owns another will help prevent unauthorized updates.
     */
    protected $owner;

    /**
     * Cached schemas have the following shape:
     * 
     * [
     *     [resource-type, implementation-name, impl-id-field],
     *     [
     *         [attribute-name, implementation-name], ...
     *     ],
     *     [
     *         [relationship-mask, relationship-name, link-or-chain], ...
     *     ]
     * ]
     * 
     * Cached Graphs are currently the only supported method of creating
     * schema instances.
     */
    public static function fromArray(array $definitions): self
    {
        list($resource, $attributes, $relationships, $owner) = $definitions;
        list($type, $src, $id) = $resource;

        $self = new self();

        $self->id = $id;
        $self->type = [$type, $src];
        $self->attributes = $attributes;
        $self->relationships = $relationships;
        $self->owner = $owner;
        
        foreach ($attributes as $attr) {
            $self->fields[] = $attr[0];
        }

        return $self; 
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type[self::TYPE_NAME];
    }

    public function getImplType(): string
    {
        return $this->type[self::TYPE_SRC];
    }

    public function hasAttribute(string $attr): bool
    {
        return isset($this->attributes[$attr]);
    }

    public function getAttributes(): array
    {
        return $this->fields;
    }

    public function getImplAttributes(): array
    {
        $attrs = [];

        // If sparse fieldsets were given, not all attributes may be selected.
        foreach ($this->fields as $attr) {
            $selected = $this->attributes[$attr];
            $attrs[] = [$selected[self::ATTR_NAME], $selected[self::ATTR_SRC]];
        }
    
        return $attrs;
    }

    public function getDefaultValueFactory(string $attr)
    {
        if ($this->attributes[$attr][self::ATTR_DEFAULT_VALE] === null) {
            return null;
        }

        return new $this->attributes[$attr][self::ATTR_DEFAULT_VALE];
    }

    public function clearFields()
    {
        $this->fields = [];
    }

    public function addField(string $field)
    {
        $this->fields[] = $field;
    }

    public function getImplAttribute(string $attr)
    {
        return $this->attributes[$attr][self::ATTR_SRC];
    }

    public function getRelationship(string $relationship): array
    {
        return $this->relationships[$relationship];
    }

    public function hasRelationship(string $relationship): bool
    {
        return isset($this->relationships[$relationship]);
    }

    public function hasOwningRelationship(): bool
    {
        return $this->owner !== null;
    }

    /**
     * Owning relationships must be of type direct belongs-to-one.
     * That is, the foreign key to the owner exists in this table; the
     * link must be a string.
     */
    public function getOwningRelationship(): string
    {
        return $this->relationships[$this->owner][2];
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Doctrine\DBAL\Query\QueryBuilder;

use ThePetPark\Library\Graph\Relationship as R;

use function sprintf;
use function array_keys;

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
     * Consumers can select what attributes to show via sparse fields.
     * Default value of zero denotes that all fields should be selected.
     * 
     * @var int
     */
    protected $select;

    /**
     * Resource attributes.
     * 
     * Attribute signature:
     * attr_name: string => [selectable: int, attr_name: string, backend_alias: string]
     * 
     * If alias is not provided, it is assumed that alias == attr_name.
     */
    protected $attributes;

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
     * A two-dimensional array containing a HTTP verb to Graph action handler.
     * First dimension applies to resources; the second is for relationships.
     * By default these will map to the Graph's NotImplemented handler.
     */
    protected $actions;

    /**
     * Cached schemas have the following shape:
     * 
     * [
     *     [resource-type, implementation-name, impl-id-field],
     *     [
     *         [0, attribute-name, implementation-name], ...
     *     ],
     *     [
     *         [relationship-mask, relationship-name, link-or-chain], ...
     *     ],
     *     [
     *         [http-verb => resource-action, ...],
     *         [http-verb => relationship-action, ...]
     *     ]
     * ]
     * 
     * Cached Graphs are currently the only supported method of creating
     * schema instances.
     */
    public static function fromArray(array $definitions): self
    {
        list($resource, $attributes, $relationships, $actions) = $definitions;
        list($type, $src, $id) = $resource;

        $self = new self();

        $self->select = 0;
        $self->id = $id;
        $self->type = [$type, $src];
        $self->attributes = $attributes;
        $self->relationships = $relationships;
        $self->actions = $actions;

        return $self; 
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type[0];
    }

    public function getImplType(): string
    {
        return $this->type[1];
    }

    public function hasAttribute(string $attr): bool
    {
        return isset($this->attributes[$attr]) || ($attr === 'id');
    }

    public function getAttributes(): array
    {
        return array_keys($this->attributes);
    }

    public function getImplAttribute(string $attr)
    {
        return ($attr === 'id') ? $this->id : $this->attributes[$attr][2];
    }

    public function getVisibleAttributes(): array
    {
        $attrs = [];

        foreach ($this->attributes as list($select, $attr, $impl)) {
            if ($select === $this->select) {
                $attrs[] = [$attr, $impl];
            }
        }

        return $attrs;
    }

    public function getRelationship(string $relationship)
    {
        return $this->relationships[$relationship] ?? null;
    }

    public function hasRelationship(string $relationship)
    {
        return isset($this->relationships[$relationship]);
    }

    public function setActionKey(int $context, string $action, int $key)
    {
        $this->actions[$context][$action] = $key;
    }

    public function getActionKey(int $context, string $action): int
    {
        return $this->actions[$context][$action];
    }

    public function applySparseFields(array $fields)
    {
        // Don't apply sparse fieldset if it was already applied.
        if ((empty($fields) === false) && ($this->select === 0)) {
            $this->select++;
    
            // Apply sparse fieldset. Fields must contain valid attributes.
            foreach ($fields as $attr) {
                $this->attributes[$attr][0]++;
            }
        }
    }

    /**
     * Adds selections to the query based on this schema's attributes.
     * Use provided enumeration $ref to uniquely identify the selected resource.
     * Sparse fields must be a resourceType -> list of attributes map.
     */
    public function addAttributesToQuery(
        QueryBuilder $qb,
        string $ref,
        array $sparseFields = []
    ) {
        // Always select the resource's ID.
        $qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s', $ref, $this->id, 'id'));

        // Add the attributes to the select statement, aliasing the fields
        // as {resource ref}_{resource attribute}
        foreach ($this->getAttributes() as list($select, $attr, $field)) {
            if ($select === $this->select) {
                $qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s', $ref, $field, $attr));
            }
        }
    }
}
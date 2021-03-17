<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Doctrine\DBAL\Query\QueryBuilder;

use ThePetPark\Library\Graph\Relationship as R;

use function sprintf;

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

    protected function attr(string $attribute, int $i): string
    {
        if (isset($this->attributes[$attribute])) {
            return $this->attributes[$attribute][$i];
        }

        return null;
    }

    public function hasAttribute(string $attribute): bool
    {
        return isset($this->attributes[$attribute]);
    }

    public function getAttribute(string $attribute)
    {
        return $this->attr($attribute, 1);
    }

    public function getImplAttribute(string $attribute)
    {
        return $this->attr($attribute, 2);
    }

    public function getSelectedAttributes(): array
    {
        $attrs = [];

        foreach ($this->attributes as $attr) {
            if ($attr[0] === $this->select) {
                $attrs[] = $attr;
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

    public function setActionKey(int $context, string $httpVerb, int $key)
    {
        $this->actions[$context][$httpVerb] = $key;
    }

    public function getActionKey(int $context, string $httpVerb): int
    {
        return $this->actions[$context][$httpVerb];
    }

    /**
     * Adds selections to the query based on this schema's attributes.
     * Use provided enumeration $ref to uniquely identify the selected resource.
     * Sparse fields must be a resourceType -> list of attributes map.
     */
    public function includeFields(QueryBuilder $qb, string $ref, array $sparseFields = [])
    {
        // Select the resource's ID.
        $qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s', $ref, $this->id, 'id'));

        $fields = $sparseFields[$this->getType()] ?? [];

        // Don't apply sparse fieldset if it was already applied.
        if ((empty($fields) === false) && ($this->select === 0)) {
            $this->select++;
    
            // Apply sparse fieldset. Fields must contain valid attributes.
            foreach ($fields as $attr) {
                $this->attributes[$attr][0]++;
            }
        }

        
        // Add the attributes to the select statement, aliasing the fields
        // as {resource enum}_{resource attribute}
        foreach ($this->attributes as list($select, $attr, $field)) {
            if ($select === $this->select) {
                $qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s', $ref, $field, $attr));
            }
        }
    }

    /**
     * Sets this schema as the source of the output. This must be called only
     * once on the source resource. The provided relation enumeration uniquely
     * identifies this resource in the query.
     */
    public function initialize(QueryBuilder $qb, string $self)
    {
        $qb->from($this->getImplType(), $self);
    }

    /**
     * Adds related schema to the query.
     * 
     * This resource's enumerated value (self) must have already been added
     * beforehand either by Schema::initialize or Schema::resolve.
     */
    public function resolve(
        Graph $graph, QueryBuilder $qb, string $relationship,
        string $self, string $related
    ): Relationship {

        list($mask, $relatedType, $link) = $this->relationships[$relationship];

        $relatedResource = $graph->get($relatedType);

        // TODO: Theoretically resource ownership and aggregation types
        //       should have no effect in relationships following a chain of
        //       relationships. Verify this is true!
        if (is_array($link)) {

            $joinOn = $self;
            $joinOnField = $this->id;
            
            foreach ($link as $i => list($pivot, $from, $to)) {
                $pivotEnum = $self . '_' . $i; // pivots need their own relation enums
                
                $qb->innerJoin($joinOn, $pivot, $pivotEnum, $qb->expr()->eq(
                    $joinOn . '.' . $joinOnField,
                    $pivotEnum . '.' . $from
                ));

                $joinOn = $pivotEnum;
                $joinOnField = $to;
            }

            // TODO: What if the chain doesn't end in the related resource's
            //       ID but in another relationship? Unlikely for this project
            //       but might want to consider other relationship fields in
            //       the future.
            $qb->innerJoin($joinOn, $relatedResource->getImplType(), $related, $qb->expr()->eq(
                $joinOn . '.' . $joinOnField,
                $related . '.'. $relatedResource->getId()
            ));

        } else {

            $joinExpr = ($mask & (R::MANY|R::OWNS))

                // foreign key is in related resource (resource owns another if
                // there exists a foreign key in the related resource)
                ? $qb->expr()->eq($self . '.' . $this->id, $related . '.' . $link)

                // foreign key is in this resource (resource is owned by related)
                : $qb->expr()->eq(
                    $self . '.' . $link,
                    $related . '.' . $relatedResource->getId()
                );

            $qb->innerJoin($self, $relatedResource->getImplType(), $related, $joinExpr);

        }

        return new Relationship($relatedResource, $mask);
    }
}
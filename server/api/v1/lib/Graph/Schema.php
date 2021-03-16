<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

use ThePetPark\Library\Graph\Relationship as R;

use Exception;

use function parse_str;
use function json_decode;
use function json_encode;

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
 * 
 * TODO: implement methods for getting relationships (ex. /articles/1/relationships/author)
 */
class Schema
{
    /**
     * Back-end alias to resource ID. Defaults to "id".
     * 
     * @var string
     */
    protected $_id = 'id';

    /**
     * Type of resource.
     * 
     * Signatures:
     * [resource_type: string, backend_alias: ?string]
     * 
     * If alias is not provided, it is assumed that alias == resource_type.
     * 
     * @var array
     */
    protected $_type = [];

    /**
     * Consumers can select what attributes to show via sparse fields.
     * Default value of zero denotes that all fields should be selected.
     * 
     * @var int
     */
    protected $_select = 0;

    /**
     * Resource attributes.
     * 
     * Attribute signature:
     * attr_name: string => [selectable: int, attr_name: string, backend_alias: string]
     * 
     * If alias is not provided, it is assumed that alias == attr_name.
     */
    protected $_attributes = [];

    /**
     * Resource relationship map. Used for combining related resources.
     * The resolution masks dictates whether or not the link comes from
     * this resource, the related resource, or a chain of relationships.
     * 
     * Relationship signature:
     * relationship_name: string => [resolution_mask: int, relatedType: string, link: string|array]
     */
    protected $_relationships = [];

    /**
     * A two-dimensional array containing a HTTP verb to Graph action handler.
     * First dimension applies to resources; the second is for relationships.
     * By default these will map to the Graph's NotImplemented handler.
     */
    /*protected $_handlers = [[
        'GET'     => Graph::ACTION_NOT_IMPL,
        'POST'    => Graph::ACTION_NOT_IMPL,
        'PUT'     => Graph::ACTION_NOT_IMPL,
        'PATCH'   => Graph::ACTION_NOT_IMPL,
        'DELETE'  => Graph::ACTION_NOT_IMPL,
    ], [
        'GET'     => Graph::ACTION_NOT_IMPL,
        'POST'    => Graph::ACTION_NOT_IMPL,
        'PUT'     => Graph::ACTION_NOT_IMPL,
        'PATCH'   => Graph::ACTION_NOT_IMPL,
        'DELETE'  => Graph::ACTION_NOT_IMPL,
    ]];*/
    protected $_actions = [];

    /**
     * Cached schemas have the following shape:
     * 
     * [
     *     [resource-type, implementation-name, primary-key-field],
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
     */
    public static function fromArray(array $definitions): self
    {
        list($resource, $attributes, $relationships, $actions) = $definitions;
        list($type, $src, $id) = $resource;

        $self = new self();

        $self->_select = 0;
        $self->_id = $id;
        $self->_type = [$type, $src];
        $self->_attributes = $attributes;
        $self->_relationships = $relationships;
        $self->_actions = $actions;

        return $self; 
    }

    public function setActionKey(int $context, string $httpVerb, int $key)
    {
        $this->_actions[$context][$httpVerb] = $key;
    }

    public function getActionKey(int $context, string $httpVerb): int
    {
        return $this->_actions[$context][$httpVerb];
    }

    public function bootstrap()
    {
        $this->definitions();

        if (empty($this->_type)) {
            throw new Exception('Resource schema must have a type');
        }
    }

    /**
     * Consumers must implement this function and use schema methods to
     * bootstrap the resource model. At the very least a type must be defined.
     * Alternatively, configuration details can be provided via a cache file.
     */
    protected function definitions() {}

    /**
     * Mapper functions pick out attributes from a raw SQL query.
     * 
     * TODO: this might be do-able from the controller since a resource's
     * schema is available from there!
     */
    //abstract public function mapper(array $fields, string $prefix): array;
    
    /**
     * Adds an attribute to the resource schema. Attributes are represented
     * as an array consisting of three elements: the selectable flag,
     * attribute name, and implementation name. Since a schema's _select flag
     * is initialized to zero, all fields will be selected by default.
     */
    protected function addAttribute(string $attr, string $impl = '')
    {
        $this->_attributes[$attr] = [0, $attr, strlen($impl) > 0 ? $impl : $attr];
    }

    /**
     * Sets the implementation name for this resource schema's ID field.
     */
    protected function setId(string $impl)
    {
        $this->_id = $impl;
    }

    protected function setType(string $type, string $alias = '')
    {
        $this->_type = [$type, strlen($alias) > 0 ? $alias : $type];
    }

    protected function hasOne(string $relationship, string $relatedType, $link)
    {
        $this->_relationships[$relationship] = [R::OWNS|R::ONE, $relatedType, $link];
    }

    protected function hasMany(string $relationship, string $relatedType, $link)
    {
        $this->_relationships[$relationship] = [R::OWNS|R::MANY, $relatedType, $link];
    }

    protected function belongsToOne(string $relationship, string $relatedType, $link)
    {
        $this->_relationships[$relationship] = [R::OWNED|R::ONE, $relatedType, $link];
    }

    /**
     * Note: if a resource belongs to many related resources, the link must be
     * a dependency chain since the foreign key must be in a pivot table.
     */
    protected function belongsToMany(string $relationship, string $relatedType, array $link)
    {
        $this->_relationships[$relationship] = [R::OWNED|R::MANY, $relatedType, $link];
    }

    public function getId(): string
    {
        return $this->_id;
    }

    public function getType(): string
    {
        return $this->_type[0];
    }

    public function getImplType(): string
    {
        return $this->_type[1];
    }

    protected function _getAttribute(string $attribute, int $i): string
    {
        if (isset($this->_attributes[$attribute])) {
            return $this->_attributes[$attribute][$i];
        }

        return null;
    }

    public function getAttribute(string $attribute)
    {
        return $this->_getAttribute($attribute, 1);
    }

    public function getImplAttribute(string $attribute)
    {
        return $this->_getAttribute($attribute, 2);
    }

    public function getRelationship(string $relationship)
    {
        return $this->_relationships[$relationship] ?? null;
    }

    public function hasRelationship(string $relationship)
    {
        return isset($this->_relationships[$relationship]);
    }

    /**
     * Applies sparse fields to the query.
     * 
     * @return bool Whether or not all fields were applied successfully.
     */
    public function setSelectableAttribtes(array $fields): bool
    {
        $success = true;

        $this->_select = 1;

        foreach ($fields as $field) {
            if (isset($this->_attributes[$field])) {
                $this->_attributes[$field][0] = 1;
            } else {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Adds selections to the query based on this schema's attributes.
     * Use provided enumeration $ref to uniquely identify the selected resource.
     */
    public function includeFields(QueryBuilder $qb, string $ref)
    {
        // Select the resource's ID.
        $qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s', $ref, $this->_id, 'id'));
        
        // Add the attributes to the select statement, aliasing the fields
        // as {resource enum}_{resource attribute}
        foreach ($this->_attributes as list($select, $attr, $field)) {
            if ($select === $this->_select) {
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
        $qb->from($this->_type[1], $self);
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
        list($mask, $relatedType, $link) = $this->_relationships[$relationship];

        $relatedResource = $graph->get($relatedType);

        // TODO: Theoretically resource ownership and resolution cardinality
        //       should have no effect in relationships following a chain of
        //       relationships. Verify this is true!
        if (is_array($link)) {

            $i = 0;
            $joinOn = $self;
            $joinOnField = $this->_id;
            
            foreach ($link as list($pivot, $from, $to)) {
                $pivotEnum = $self . '_' . $i; // pivots need their own relation enums
                
                $qb->innerJoin($joinOn, $pivot, $pivotEnum, $qb->expr()->eq(
                    $joinOn . '.' . $joinOnField,
                    $pivotEnum . '.' . $from
                ));

                $joinOn = $pivotEnum;
                $joinOnField = $to;
                $i++;
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
                ? $qb->expr()->eq($self . '.' . $this->_id, $related . '.' . $link)

                // foreign key is in this resource (resource is owned by related)
                : $qb->expr()->eq($self . '.' . $link, $related . '.' . $relatedResource->getId());

            $qb->innerJoin($self, $relatedResource->getImplType(), $related, $joinExpr);

        }

        return new Relationship($relatedResource, $mask);
    }
}
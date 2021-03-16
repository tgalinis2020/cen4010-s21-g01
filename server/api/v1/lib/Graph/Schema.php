<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

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
abstract class Schema
{
    // Relationship resolution bitfield values
    const ONE      = 1;
    const MANY     = 2;
    const OWNS     = 4;
    const OWNED    = 8;

    /**
     * Back-end alias to resource ID. Defaults to "id".
     */
    protected $_id = 'id';

    /**
     * Type of resource.
     * 
     * Signatures:
     * [resource_type: string, backend_alias: ?string]
     * 
     * If alias is not provided, it is assumed that alias == resource_type.
     */
    protected $_type = [];
    

    /**
     * Resource attributes.
     * 
     * Attribute signature:
     * attr_name: string => backend_alias: ?string
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


    // By default, data model manipulation methods will return a
    // 501 Not Implemented if not overridden.
    public function create(Connection $conn, Request $req, Response $res): Response
    {
        return $res->withStatus(501);
    }

    public function replace(Connection $conn, Request $req, Response $res): Response
    {
        return $res->withStatus(501);
    }

    public function update(Connection $conn, Request $body, Response $res): Response
    {
        return $res->withStatus(501);
    }

    public function delete(Connection $conn, Request $body, Response $res): Response
    {
        return $res->withStatus(501);
    }

    public function createRelationship(Connection $conn, Request $req, Response $res): Response
    {
        return $res->withStatus(501);
    }

    public function updateRelationship(Connection $conn, Request $body, Response $res): Response
    {
        return $res->withStatus(501);
    }

    public function deleteRelationship(Connection $conn, Request $body, Response $res): Response
    {
        return $res->withStatus(501);
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
     * bootstrap the resource model.
     * 
     * Models without a set type will result in undefined behavior.
     */
    abstract protected function definitions();

    /**
     * Mapper functions pick out attributes from a raw SQL query.
     * 
     * TODO: this might be do-able from the controller since a resource's
     * schema is available from there!
     */
    //abstract public function mapper(array $fields, string $prefix): array;
    
    protected function addAttribute(string $attr, string $alias = '')
    {
        $this->_attributes[$attr] = [$attr, strlen($alias) > 0 ? $alias : $attr];
    }

    protected function setIdField(string $id)
    {
        $this->_id = $id;
    }

    protected function setType(string $type, string $alias = '')
    {
        $this->_type = [$type, strlen($alias) > 0 ? $alias : $type];
    }

    protected function hasOne(string $relationship, string $relatedType, $link)
    {
        $this->_relationships[$relationship] = [Schema::OWNS | Schema::ONE, $relatedType, $link];
    }

    protected function hasMany(string $relationship, string $relatedType, $link)
    {
        $this->_relationships[$relationship] = [Schema::OWNS | Schema::MANY, $relatedType, $link];
    }

    protected function belongsToOne(string $relationship, string $relatedType, $link)
    {
        $this->_relationships[$relationship] = [Schema::OWNED | Schema::ONE, $relatedType, $link];
    }

    /**
     * Note: if a resource belongs to many related resources, the link must be
     * a dependency chain since the foreign key must be in a pivot table.
     */
    protected function belongsToMany(string $relationship, string $relatedType, array $link)
    {
        $this->_relationships[$relationship] = [Schema::OWNED | Schema::MANY, $relatedType, $link];
    }

    public function getId(): string
    {
        return $this->_id;
    }

    public function getType(): string
    {
        return $this->_type[0];
    }

    public function getTypeImpl(): string
    {
        return $this->_type[1];
    }

    public function getAttribute(string $attribute)
    {
        return $this->_attributes[$attribute] ?? null;
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
     * Adds selections to the query based on this schema's attributes.
     * Use provided enumeration "self" to uniquely identify the selected resource.
     */
    public function includeFields(QueryBuilder $qb, string $self)
    {
        // Select the resource's ID.
        $qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s', $self, $this->_id, 'id'));
        
        // Add the attributes to the select statement, aliasing the fields
        // as {resource enum}_{resource attribute}
        foreach ($this->_attributes as list($attr, $field)) {
            $qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s', $self, $field, $attr));
        }
    }

    /**
     * Sets this schema as the source of the output. This must be called only
     * once on the source resource. The provided relation enumeration uniquely
     * identifies this resource in the query.
     */
    public function initialize(QueryBuilder $qb, string $self)
    {
        $qb->select()->from($this->_type[1], $self);
    }

    /**
     * Adds related schema to the query.
     * 
     * This resource's enumerated value (self) must have already been added
     * beforehand either by Schema::resolve or Schema::resolveRelationship.
     */
    public function resolve(
        Graph $graph, QueryBuilder $qb, string $relationship,
        string $self, string $related
    ): array {
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
                $pivotEnum = $self . '_p' . $i; // pivots need their own relation enums
                
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
            $qb->innerJoin($joinOn, $relatedResource->getTypeImpl(), $related, $qb->expr()->eq(
                $joinOn . '.' . $joinOnField,
                $related . '.'. $relatedResource->getId()
            ));

        } else {

            $joinExpr = ($mask & (Schema::MANY | Schema::OWNS))

                // foreign key is in related resource (resource owns another if
                // there exists a foreign key in the related resource)
                ? $qb->expr()->eq($self . '.' . $this->_id, $related . '.' . $link)

                // foreign key is in this resource (resource is owned by related)
                : $qb->expr()->eq($self . '.' . $link, $related . '.' . $relatedResource->getId());

            $qb->innerJoin($self, $relatedResource->getTypeImpl(), $related, $joinExpr);

        }

        return [$relatedResource, $mask];
    }

    public function filter(
        Graph $graph, QueryBuilder $qb, CompositeExpression $conditions,
        string $self
    ) {
        // TODO: If applicable, this should follow a join resolution.
        //       Make sure this is always the case.

        // TODO: apply conditions to query using enuerated value "self"

        // TODO: could probably do this from Graph::resolve
    }

    /**
     * At this point, queries have been executed.
     * Parse the input data for any included resources.
     * Must return an array with 2 elements: the main & included resources.
     */
    public function finalize()
    {
        // stub
    }
}
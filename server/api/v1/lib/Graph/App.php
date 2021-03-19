<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;

use ThePetPark\Library\Graph\Relationship as R;

use function file_exists;
use function class_exists;
use function in_array;

/**
 * Resource schema container.
 * 
 * Uses DBAL's QueryBuilder to create queries based on the structure of the
 * request.
 * 
 * @author Thomas Galinis <tgalinis2020@fau.edu>
 */
class App implements RequestHandlerInterface, ResponseFactoryInterface
{
    const ACTION_NOT_IMPL = 0;

    // Context types
    const RESOURCE_CONTEXT     = 0;
    const RELATIONSHIP_CONTEXT = 1;

    // These constants hold request attribute keys that must exist in the
    // request. Except for context, an adapter must map them to routes.
    const PARAM_CONTEXT       = 'graph_context';
    const PARAM_RESOURCE      = 'graph_resource';
    const PARAM_ID            = 'graph_id';
    const PARAM_RELATIONSHIP  = 'graph_relationship';
    
    const SUPPORTED_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
    
    const REF_PREFIX = 'r';

    /** @var \Doctrine\DBAL\Connection */
    protected $conn;

    /** @var Schema[] */
    protected $schemas = [];

    /** @var string[] */
    protected $actions = [];

    /** @var int */
    protected $nactions = 0;

    /** @var array */
    protected $settings;

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /** @var \Psr\Http\Message\ResponseInterface */
    protected $response;

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
     * The source of every resource. Its value will be used as a prefix for all
     * references added to the reference table.
     * 
     * @var string
     */
    protected $tokenPrefix;

    /**
     * Reference to the source of data.
     * 
     * @var string
     */
    protected $baseRef;

    public function __construct(
        Connection $conn,
        ResponseInterface $response,
        array $settings = [],
        $container = null
    ) {
        $this->conn = $conn;
        $this->response = $response;
        $this->container = $container;
        
        $pagination = $settings['pagination'] ?? [];

        $this->settings = [
            'definitions' => $settings['definitions'] ?? null,
            'pagination' => [
                'maxPageSize' => $pagination['maxPageSize'] ?? 20,
            ],
            'features' => $settings['features'] ?? [
                Features\Pagination\Cursor::class,
                Features\Filters\Simple::class,
                Features\Sorting\Simple::class,
            ],
        ];

        if ((($f = $this->settings['definitions']) !== null) && file_exists($f)) {
            $this->parseArray(require $f);
        } else {
            throw new Exception(
                'Compiled definitions file is required to initialize the Graph. '
                . 'Create a YAML definitions file and use bin/graph to compile them.'
            );
        }
    }

    /**
     * Initialize the Graph using a definitions array.
     * Note that if string keys are mapped to actions, a dependency container
     * must be set since the action needs to be instantiated.
     * 
     * If a container was not provided, the action listed in the cachke file
     * must be a fully qualified name of a class that implements the
     * ActionInterface.
     */
    private function parseArray(array $definitions)
    {
        list($actions, $schemas) = $definitions;
        
        foreach ($actions as $action) {
            $this->actions[$this->nactions++] = $action;
        }
        
        foreach ($schemas as $schema) {
            $this->addSchema(Schema::fromArray($schema));
        }
    }

    public function applyFeatures(QueryBuilder $queryBuilder, array &$parameters)
    {
        foreach ($this->settings['features'] as $feature) {
            $feat = new $feature;

            if ($feat->check($parameters)) {
                $feat->apply($this, $queryBuilder, $parameters);
                $feat->clean($parameters);
            }
        }
    }

    /**
     * Sets provided schema as the source of the output. This must be called only
     * once on the source resource.
     */
    public function initialize(QueryBuilder $qb, Schema $schema): string
    {
        $qb->from($schema->getImplType(), $this->baseRef);

        return $this->baseRef;
    }

    /**
     * Adds selections to the query based on this schema's attributes.
     * Use provided enumeration $ref to uniquely identify the selected resource.
     * Sparse fields must be a resourceType -> list of attributes map.
     */
    public function addSchemaToQuery(
        Schema $schema,
        QueryBuilder $qb,
        string $ref
    ) {
        // Always select the resource's ID.
        $qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s', $ref, $this->id, 'id'));

        // Add the attributes to the select statement, aliasing the fields
        // as {resource ref}_{resource attribute}
        foreach ($schema->getVisibleAttributes() as list($attr, $impl)) {
            $qb->addSelect(sprintf('%1$s.%2$s %1$s_%3$s', $ref, $impl, $attr));
        }
    }

    /**
     * Adds related schema to the query.
     * Creates a new reference in the graph's reference table.
     * 
     * This resource's enumerated value (self) must have already been added
     * beforehand either by Schema::initialize or Schema::resolve.
     */
    public function resolve(
        string $relationship,
        string $ref,
        QueryBuilder $qb
    ): Relationship {

        list($mask, $relatedType, $link) = $this->relationships[$relationship];

        $schema = $this->getSchema($relatedType);
        $relatedRef = $this->createRef($relationship, $relatedType, $ref);

        // TODO: Theoretically resource ownership and aggregation types
        //       should have no effect in relationships following a chain of
        //       relationships. Verify this is true!
        if (is_array($link)) {

            $joinOn = $ref;
            $joinOnField = $this->id;
            
            foreach ($link as $i => list($pivot, $from, $to)) {
                $pivotEnum = $ref . '_' . $i; // pivots need their own relation enums
                
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
            $qb->innerJoin($joinOn, $schema->getImplType(), $relatedRef, $qb->expr()->eq(
                $joinOn . '.' . $joinOnField,
                $relatedRef . '.'. $schema->getId()
            ));

        } else {

            $joinExpr = ($mask & (R::MANY|R::OWNS))

                // foreign key is in related resource (resource owns another if
                // there exists a foreign key in the related resource)
                ? $qb->expr()->eq($ref . '.' . $this->id, $relatedRef . '.' . $link)

                // foreign key is in this resource (resource is owned by related)
                : $qb->expr()->eq(
                    $ref        . '.' . $link,
                    $relatedRef . '.' . $schema->getId()
                );

            $qb->innerJoin($ref, $schema->getImplType(), $relatedRef, $joinExpr);

        }

        return new Relationship($relatedRef, $schema, $mask);
    }

    public function getMaxPageSize(): int
    {
        return $this->settings['pagination']['maxPageSize'];
    }

    public function getConnection(): Connection
    {
        return $this->conn;
    }

    public function getSchema(string $resource)
    {
        return $this->schemas[$resource] ?? null;
    }

    public function addSchema(Schema $schema)
    {
        $this->schemas[$schema->getType()] = $schema;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $source  = $request->getAttribute(self::PARAM_RESOURCE);
        $schema  = $this->getSchema($source);
        
        if ($schema === null) {
            // TODO: consider making handers that can handle this event.
            return $this->response->withStatus(404);
        }
        
        $method = $request->getMethod();
        
        if (in_array($method, self::SUPPORTED_METHODS) === false) {
            return $this->response->withStatus(505);
        }
        
        $context = $request->getAttribute(self::PARAM_CONTEXT);
        $actionClass = $this->actions[$schema->getActionKey($context, $method)];
        $ref = $this->getLatestRef();

        if ($this->container !== null) {
            $action = $this->container->get($actionClass);
        } elseif (class_exists($actionClass)) {
            $action = new $actionClass;
        } else {
            throw new Exception(sprintf(
                'Cannot create instance of %s action',
                $actionClass
            ));
        }

        // Initialize graph reference table properties
        $this->baseRef = $ref;
        $this->tokenPrefix = $source;
        $this->resourceTypes[$ref] = $source;
        $this->raw[$ref] = [];
        $this->data[$ref] = [];
        $this->relationships[$ref] = [];
        
        return $action->execute($this, $request);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->response
            ->withHeader('Content-Type', 'application/vnd.api+json')
            ->withStatus($code, $reasonPhrase);
    }

    public function createRef(string $token, string $resourceType, string $parentRef)
    {
        $ref = self::REF_PREFIX . (++$this->ref);
        $token = $this->tokenPrefix . '.' . $token;

        $this->references[$token] = $ref;
        $this->resourceTypes[$ref] = $resourceType;
        
        // Keep track of child-to-parent relationships; required for propagating
        // data to the parent resource's rel map.
        $this->parentRefs[$ref] = $parentRef;

        // If a parent is provided, a relationship name *should* be
        // available. Relationship tokens are delimited with a period.
        $this->relationshipNames[$ref] = substr(strrchr($token, '.'), 1) ?: '';

        $this->raw[$ref] = [];
        $this->data[$ref] = [];
        $this->relationships[$ref] = [];

        return $ref;
    }

    public function addChildRef(string $ref, string $childRef)
    {
        $this->parentRefs[$ref][] = $childRef;
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

    public function getRefByToken(string $token): string
    {
        return $this->references[$token];
    }
    
    public function getSchemaByRef(string $ref)
    {
        return $this->schemas[$this->getResourceType($ref)];
    }

    /**
     * If selecting data derived from a resource's relationship, the
     * reference to the relationship should be promoted to the base reference.
     */
    public function promoteRef(string $ref)
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
        return self::REF_PREFIX . $this->ref;
    }

    public function getRootRef(): string
    {
        return self::REF_PREFIX . '0';
    }

    public function scanIncluded(array $raw)
    {
        foreach ($this->parentRefs as $ref => $parentRef) {
            $schema = $this->getSchemaByRef($ref);
            $prefix = $ref . '_';
            $id = $prefix . 'id';
            $type = $this->resourceTypes[$ref];

            foreach ($schema->getAttributes() as $attribute) {
                $value = $raw[$prefix . $attribute];
                
                // TODO: some refs might not have been used to select data.
                //       this can happen when applying filters!
            }
        }
    }
}

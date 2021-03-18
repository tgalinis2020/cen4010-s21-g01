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
        
        $p = $settings['pagination'] ?? [];

        $this->settings = [
            'definitions' => $settings['definitions'] ?? null,
            'pagination' => [
                'maxPageSize' => $p['maxPageSize'] ?? 20,
            ],
            'features' => [
                Features\Pagination\Cursor::class,
                Features\Filtering\Simple::class,
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

        foreach ($schemas as $schema) {

            $this->addSchema(Schema::fromArray($schema));

        }

        foreach ($actions as $action) {

            if ($this->container !== null) {

                $this->actions[$this->nactions++] = $this->container->get($action);

            } elseif (class_exists($action)) {
                
                $this->actions[$this->nactions++] = $action;
    
            } else {

                throw new Exception(sprintf(
                    'Cannot create instance of %s action',
                    $action
                ));
            }

        }

    }

    public function applyFeatures(QueryBuilder $queryBuilder, array $parameters)
    {
        foreach ($this->settings['features'] as $feature) {
            $feat = new $feature;

            if ($feat->check($parameters)) {
                $feat->apply($this, $queryBuilder, $parameters);
            }
        }
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

        $action = $this->container === null
            ? new $actionClass
            : $this->container->get($actionClass); 

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
}

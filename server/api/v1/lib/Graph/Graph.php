<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Doctrine\DBAL\Connection;

use Exception;
use Psr\Container\ContainerInterface;

use function file_exists;

/**
 * Resource schema container.
 * 
 * Uses DBAL's QueryBuilder to create queries based on the structure of the
 * request.
 * 
 * @author Thomas Galinis <tgalinis2020@fau.edu>
 */
class Graph
{
    /**
     * The root action is the Graph itself. If no schema actions
     * were defined beforehand, the Graph will return a 501 Not Implemented.
     */
    const ACTION_NOT_IMPL    = 0;
    
    /**
     * Required to differetiate actions between resources and relationships.
     * Must be present in the request as an attribute.
     */
    const CONTEXT            = 'graph_context';

    // Context types
    const RESOURCE           = 0;
    const RELATIONSHIP       = 1;
    
    // These constants hold request attribute keys that must exist in the
    // request. An adapter must map them to routes.
    const RESOURCE_TYPE      = 'graph_type';
    const RESOURCE_ID        = 'graph_id';
    const RELATIONSHIP_TYPE  = 'graph_relationship';
    const ID_REGEX_INT       = '[0-9]+';

    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    /** @var Schema[] */
    private $schemas = [];

    /** @var \ThePetPark\Library\Graph\ActionInterface[] */
    private $actions = [];

    /** @var int */
    private $nactions = 0;

    /**
     * @var array
     */
    private $settings = [
        'defaultPageSize' => 12,
        'cache' => null,
    ];

    /** @var \Psr\Container\ContainerInterface */
    private $container = null;

    public function __construct(Connection $conn, array $settings = [])
    {
        $this->conn = $conn;
        $this->settings += $settings;

        if ((($f = $this->settings['cache']) !== null) && file_exists($f)) {
            $this->parseArray(require $f);
        } else {
            // Cached definitions already include the NotImplemented handler.
            $this->actions[$this->nactions++] = new Handlers\NotImplemented();
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

            $this->add(Schema::fromArray($schema));

        }

        foreach ($actions as $action) {

            if ($this->container !== null) {

                $this->actions[$this->nactions++] = $this->container->get($action);

            } elseif (class_exists($action)) {
                
                $this->actions[$this->nactions++] = new $action;
    
            } else {

                throw new Exception('Cannot create instance of action ' . $action);
            }

        }

  
    }

    /**
     * If actions are defined in a PSR-11 container, Graph can try to resolve
     * them from it instead of instantiating them itself. A container is
     * required if actions have dependencies injected in their constructor.
     */
    public function setContainer(ContainerInterface $c)
    {
        $this->container = $c;
    }

    public function getDefaultPageSize(): int
    {
        return $this->settings['defaultPageSize'];
    }
    /**
     * The Graph itself is an ActionHandler.
     * It reports that the current operation is unsupported.
     */
    public function execute(
        Graph $graph,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        return $response->withStatus(501);
    }

    public function getConnection(): Connection
    {
        return $this->conn;
    }

    public function addDefinitions(string $filename)
    {
        foreach ((require $filename) as $cls) {
            $this->add(new $cls);
        }
    }

    public function get(string $resource)
    {
        return $this->schemas[$resource] ?? null;
    }

    public function add(Schema $schema)
    {
        $schema->bootstrap();
        $this->schemas[$schema->getType()] = $schema;
    }

    /**
     * Adds handler to collection of handlers. Returns its key.
     */
    public function addAction(ActionInterface $action): int
    {
        $this->actions[$this->nactions] = $action;
        return $this->nactions++;
    }

    /**
     * Sets every schema's handler indexed by verb to the handler pointed by
     * the provided key.
     * 
     * Precondition: all schemas must be registered in the graph!
     */
    public function setDefaultHandler(int $context, string $httpVerb, int $key)
    {
        foreach ($this->schemas as $schema) {
            $schema->setActionKey($context, $httpVerb, $key);
        }
    }

    public function resolve(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {

        $ctx = $request->getAttribute(Graph::CONTEXT);
        $type = $request->getAttribute(Graph::RESOURCE_TYPE);
        $relationship = $request->getAttribute(Graph::RELATIONSHIP_TYPE);
        $schema = $this->get($type);

        if ($ctx === null) {
            throw new Exception('Graph::CONTEXT request attribute is missing');
        }

        // Can't resolve a relationship if it wasn't specified.
        if ($ctx === Graph::RELATIONSHIP && $relationship === null) {
            return $response->withStatus(400);
        }

        if ($schema === null) {
            return $response->withStatus(404);
        }

        $action = $this->actions[$schema->getActionKey($ctx, $request->getMethod())];
        
        return $action->execute($this, $request, $response);

    }
}

<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Exception;

use function file_exists;
use function class_exists;
use function array_merge;
use function explode;
use function count;

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
    const ACTION_NOT_IMPL     = 0;

    // Context types
    const RESOURCE            = 0;
    const RELATIONSHIP        = 1;
    
    // These constants hold request attribute keys that must exist in the
    // request. An adapter must map them to routes.
    const TOKENS              = 'graph_tokens';
    const PARAM_CONTEXT       = 'graph_context';
    const PARAM_RESOURCE      = 'graph_resource';
    const PARAM_ID            = 'graph_id';
    const PARAM_RELATIONSHIP  = 'graph_relationship';

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
        'cache'           => null,
    ];

    /** @var \Psr\Container\ContainerInterface */
    private $container;

    public function __construct(Connection $conn, array $settings = [], $container = null)
    {
        $this->conn = $conn;
        $this->settings = array_merge($this->settings, $settings);
        $this->container = $container;

        if ((($f = $this->settings['cache']) !== null) && file_exists($f)) {
            $this->parseArray(require $f);
        } else {
            throw new Exception(
                'A YAML configuration file is required to initialize the Graph. '
                . 'Use bin/graph to create definitions'
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

            $this->add(Schema::fromArray($schema));

        }

        foreach ($actions as $action) {

            if ($this->container !== null) {

                $this->actions[$this->nactions++] = $this->container->get($action);

            } else/*if (class_exists($action))*/ {
                
                $this->actions[$this->nactions++] = new $action;
    
            } /*else {

                throw new Exception(sprintf(
                    'Cannot create instance of %s action',
                    $action
                ));
            }*/

        }

    }

    public function getDefaultPageSize(): int
    {
        return $this->settings['defaultPageSize'];
    }

    public function getConnection(): Connection
    {
        return $this->conn;
    }

    public function get(string $resource)
    {
        return $this->schemas[$resource] ?? null;
    }

    public function add(Schema $schema)
    {
        $this->schemas[$schema->getType()] = $schema;
    }

    /**
     * Since this app was developed with Slim 3 in mind, it expects tokens in
     * the request attributes in the form of a string with each token delimited
     * with a forward-slash.
     */
    public function resolve(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {

        $tokens = explode('/', $request->getAttribute(Graph::TOKENS, []));
        $context = Graph::RESOURCE;
        $source = null;
        $id = null;
        $relationship = null;

        switch (count($tokens)) {
        case 4:
            if ($tokens[2] === 'relationship') {
                $context = Graph::RELATIONSHIP;
                $tokens = [$tokens[0], $tokens[1], $tokens[3]];
            } else {
                $response->withStatus(400);     // Malformed request
            }

        case 3:
            $relationship = $tokens[2];
        case 2:
            $id = $tokens[1];
        case 1:
            $source = $tokens[0];
            break;

        default:
            return $response->withStatus(400);  // Malformed request
        }

        $schema = $this->get($source);

        if ($schema === null) {
            return $response->withStatus(404);  // Resource not found
        }

        $action = $this->actions[$schema->getActionKey($context, $request->getMethod())];
        
        return $action->execute(
            $this,
            $request
                ->withAttribute(Graph::PARAM_CONTEXT,      $context)
                ->withAttribute(Graph::PARAM_RESOURCE,     $source)
                ->withAttribute(Graph::PARAM_ID,           $id)
                ->withAttribute(Graph::PARAM_RELATIONSHIP, $relationship),
            $response
        );

    }
}

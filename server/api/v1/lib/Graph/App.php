<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Exception;

use function file_exists;
use function class_exists;
use function in_array;
use function sprintf;

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
    // Default action IDs
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

    /** @var \ThePetPark\Library\Graph\AbstractDriver */
    protected $driver;

    /** @var Schema\Container */
    protected $schemas;

    /** @var string[] */
    protected $actions = [];

    /** @var int */
    protected $nactions = 0;

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /** @var \Psr\Http\Message\ResponseInterface */
    protected $response;

    public function __construct(
        string $pathToDefinitions,
        AbstractDriver $driver,
        ResponseInterface $response,
        $container = null
    ) {
        $actions = [];
        $schemas = [];
        $nactions = 0;

        if (file_exists($pathToDefinitions)) {
            list($_actions, $_schemas) = (require $pathToDefinitions);
        
            foreach ($_actions as $action) {
                $actions[$nactions++] = $action;
            }
            
            foreach ($_schemas as $_schema) { 
                $schema = Schema::fromArray($_schema);
                $schemas[$schema->getType()] = $schema;
            }
        } else {
            throw new Exception(
                'Compiled definitions file is required to initialize the Graph. '
                . 'Create a YAML definitions file and use bin/graph to compile them.'
            );
        }

        $this->schemas      = new Schema\Container($schemas);
        $this->driver       = $driver;
        $this->response     = $response;
        $this->container    = $container;
        $this->nactions     = $nactions;
        $this->actions      = $actions;
    }

    public function getDriver(): AbstractDriver
    {
        return $this->driver;
    }

    public function getSchemas(): Schema\Container
    {
        return $this->schemas;
    }

    // Since we're using sequential integers for this project, this is fine.
    // TODO: create a new component to compare specific ID types, such as
    // UUIDv1.
    public function cmp(string $a, string $b): int
    {
        return ((int) $a) - ((int) $b);
    }
    
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->response
            ->withHeader('Content-Type', 'application/vnd.api+json')
            ->withStatus($code, $reasonPhrase);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $source = $request->getAttribute(self::PARAM_RESOURCE);
        $schema = $this->schemas->get($source);
        
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
        
        return $action->execute($this, $request);
    }
}

<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Adapters;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ThePetPark\Library\Graph\Graph;
use Slim\App;
use Slim\Http\Response;

/**
 * Maps Graph actions to Slim 3 application routes.
 * 
 * To use it, add the adapter to the application's dependency container and
 * mount the it as a route group.
 * 
 * Example:
 * $app->group('/api', Graph\Adapters\SlimAdapter::class);
 */
class SlimAdapter
{
    /** @var \ThePetPark\Library\Graph\Graph */
    private $graph;

    /** @var string */
    private $regex;

    public function __construct(Graph $graph, string $regex = Graph::ID_REGEX_INT)
    {
        $this->graph = $graph;
        $this->regex = $regex;
    }

    public function __invoke(App $api)
    {
        $httpVerbs     = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']; // TODO: What about HEAD and OPTIONS?
        $root          =         '/{' . Graph::RESOURCE_TYPE. '}';
        $item          = $root . '[/{' . Graph::RESOURCE_ID . ':' . $this->regex . '}';
        $resource      = $item . '[/{' . Graph::RELATIONSHIP_TYPE . '}]]';
        $relationship  = $item . '[/relationship/{' . Graph::RELATIONSHIP_TYPE . '}]]';

        $api->map($httpVerbs, $resource,     [$this, 'resource']);
        $api->map($httpVerbs, $relationship, [$this, 'relationship']);
    }

    protected function resource(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        return $this->handle(Graph::RESOURCE, $request, $response);
    }

    protected function relationship(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        return $this->handle(Graph::RELATIONSHIP, $request, $response);
    }

    protected function handle(
        int $context,
        ServerRequestInterface $req,
        ResponseInterface $res
    ): ResponseInterface {
        return $this->graph->resolve(
            $req->withAttribute(Graph::CONTEXT, $context),
            $res
        );
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ThePetPark\Schema;

/**
 * Returns a 404 if the resource's specified relationship in the URL does not
 * exist.
 */
final class CheckRelationship
{
    /** @var \ThePetPark\Graph\Schemas\Container */
    private $schemas;

    public function __construct(Schema\Container $schemas)
    {
        $this->schemas = $schemas;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {

        /** @var \Slim\Route */
        $route = $request->getAttribute('route');

        $schema = $this->schemas->get($route->getArgument('resource'));
        
        if ($schema->hasRelationship($route->getArgument('relationship')) === false) {
            return $response->withStatus(404);
        }

        return $next($request, $response);
    
    }
}
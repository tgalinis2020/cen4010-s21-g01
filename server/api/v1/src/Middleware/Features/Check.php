<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ThePetPark\Library\Graph\Schema;

/**
 * Returns a 404 if the resource specified in the URL does not exist.
 */
final class Check
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
        
        if ($this->schemas->has($route->getArgument('resource')) === false) {
            return $response->withStatus(404);
        }

        return $next($request, $response);
    
    }
}
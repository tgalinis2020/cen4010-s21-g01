<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Auth\Guard;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Middleware\Auth;

/**
 * The users collection is an exceptional endpoint: unauthenticated users
 * must be able to create an account for themsevles.
 * 
 * This middleware allows for unauthenticated users to continue if the request
 * is made against the users collection. Access will be denied for any other
 * collection.
 */
final class Permissive
{
    public function __invoke(Request $req, Response $res, callable $next)
    {
        $session = $req->getAttribute(Auth\Session::TOKEN);

        /** @var \Slim\Route */
        $route = $req->getAttribute('route');

        $resource = $route->getArgument('resource');

        if ($resource !== 'users' && $session === null) {
            return $res->withStatus(401);
        }
            
        return $next($req, $res);
    }
}
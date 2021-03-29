<?php

declare(strict_types=1);

namespace ThePetPark\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Protects endpoints from unauthenticated users.
 */
final class Guard
{
    public function __invoke(Request $req, Response $res, callable $next)
    {
        if ($req->getAttribute(Session::TOKEN) === null) {
            return $res->withStatus(401);
        }
            
        return $next($req, $res);
    }
}
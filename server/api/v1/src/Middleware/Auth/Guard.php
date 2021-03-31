<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Protects endpoints from unauthenticated users.
 * Session middleware should precede this.
 */
final class Guard
{
    public function __invoke(Request $req, Response $res, callable $next)
    {
        $session = $req->getAttribute(Session::TOKEN);

        if ($session === null) {
            return $res->withStatus(401);
        }
            
        return $next($req, $res);
    }
}
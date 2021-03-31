<?php

declare(strict_types=1);

namespace ThePetPark\Http\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Middleware\Auth\Session;

/**
 * Unsets the session cookie if it is present.
 */
final class Delete
{
    public function __invoke(Request $req, Response $res): Response
    {
        if (isset($_COOKIE[Session::TOKEN]) === false) {
            return $res->withStatus(401); // No token found, return a 401.
        }

        // A cookie with no expiry will be immediately unset.
        setcookie(Session::TOKEN);

        return $res->withStatus(204);
    }
}


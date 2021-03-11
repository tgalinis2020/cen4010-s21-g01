<?php

declare(strict_types=1);

namespace ThePetPark\Http\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Unsets the session cookie if it is present.
 */
final class Logout
{
    public function __invoke(Request $req, Response $res): Response
    {
        if (!isset($_COOKIE['session_token'])) {
            return $res->withStatus(404); // No token found, return a 404.
        }

        // A cookie with no expiry will be immediately unset.
        setcookie('session_token');

        return $res->withStatus(200);
    }
}


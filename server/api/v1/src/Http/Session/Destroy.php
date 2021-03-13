<?php

declare(strict_types=1);

namespace ThePetPark\Http\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Unsets the session cookie if it is present.
 */
final class Destroy
{
    public function __invoke(Request $req, Response $res): Response
    {
        if (!isset($_COOKIE['session'])) {
            return $res->withStatus(401); // No token found, return a 401.
        }

        // A cookie with no expiry will be immediately unset.
        setcookie('session');

        return $res->withStatus(200);
    }
}


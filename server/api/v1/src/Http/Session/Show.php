<?php

declare(strict_types=1);

namespace ThePetPark\Http\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use function json_encode;

/**
 * Since HttpOnly cookies cannot be read by JavaScript, this endpoint will
 * give the client application the authenticated user's details.
 * 
 * Return Codes:
 *   - 200 if session token is set
 *   - 404 if session token is not set
 */
final class Show
{
    public function __invoke(Request $req, Response $res): Response
    {
        $session = $req->getAttribute('session');

        if ($session === null) {
            return $res->withStatus(404);
        }

        $res->getBody()->write(json_encode(['data' => $session]));

        return $res->withStatus(200);
    }
}


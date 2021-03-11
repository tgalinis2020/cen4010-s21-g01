<?php

declare(strict_types=1);

namespace ThePetPark\Http\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use function json_encode;

/**
 * Since HttpOnly cookies cannot be read by JavaScript, this endpoint will
 * give the client application the authenticated user's details.
 * 
 * Return Codes:
 *   - 401 if session token is not set
 *   - 200 if session token is set
 */
final class EchoSession
{
    public function __invoke(Request $req, Response $res): Response
    {
        $data = $req->getAttribute('@session');

        if ($data === null) {
            return $res->withStatus(401);
        }

        $user = [
            'username'  => $data['username'],
            'firstName' => $data['firstName'],
            'lastName'  => $data['lastName'],
            'email'     => $data['email'],
            'avatarUrl' => $data['avatarUrl'],
            'createdAt' => $data['createdAt'],
        ];

        $res->getBody()->write(json_encode(['data' => $user]));

        return $res->withStatus(200);
    }
}


<?php

declare(strict_types=1);

namespace ThePetPark\Http\Auth;

use Slim\Http\Request;
use Slim\Http\Response;
use Firebase\JWT\JWT;

/**
 * Since HttpOnly cookies cannot be read by JavaScript, this endpoint will
 * give the client application the authenticated user's details.
 * 
 * Return Codes:
 *   - 404 if session token is not set
 *   - 200 if session token is set
 */
final class EchoSession
{
    /** @var string */
    private $key;

    /** @var array */
    private $allowedAlgs;

    public function __construct(string $key, array $allowedAlgs)
    {
        $this->key = $key;
        $this->allowedAlgs = $allowedAlgs;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        $token = $req->getAttribute('@session');

        if ($token === null) {
            return $res->withStatus(404);
        }

        $data = (array) JWT::decode($token, $this->key, $this->allowedAlgs);

        // API fields from the front-end are displayed using camelCase.
        $user = [
            'username'  => $data['username'],
            'firstName' => $data['first_name'],
            'lastName'  => $data['last_name'],
            'email'     => $data['email'],
            'avatar'    => $data['avatar_url'],
        ];

        return $res->withJson(['data' => $user], 200);
    }
}


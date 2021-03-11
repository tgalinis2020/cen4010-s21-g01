<?php

declare(strict_types=1);

namespace ThePetPark\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;
use Firebase\JWT\JWT;
use Exception;

/**
 * Adds user session details to the request as an attribute using the "@session"
 * key if a session cookie is set.
 */
final class SessionMiddleware
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

    public function __invoke(Request $req, Response $res, callable $next)
    {
        $token = $_COOKIE['session_token'] ?? null;

        if ($token == null) {
            return $next($req, $res);
        }

        try {

            $data = (array) JWT::decode($token, $this->key, $this->allowedAlgs);

            return $next($req->withAttribute('@session', $data), $res);

        } catch (Exception $e) {

            // TODO: handle expired token
            return $next($req, $res);

        }
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Exception;

/**
 * Adds user session details to the request as an attribute using the "@session"
 * key if a session cookie is set.
 */
final class SessionMiddleware
{
    /** @var callable */
    private $decoder;

    public function __construct(callable $jwtDecoder)
    {
        $this->decoder = $jwtDecoder;
    }

    public function __invoke(Request $req, Response $res, callable $next)
    {
        $token = $_COOKIE['session_token'] ?? null;

        if ($token == null) {
            return $next($req, $res);
        }

        try {

            $data = (array) ($this->decoder)($token);

            return $next($req->withAttribute('@session', $data), $res);

        } catch (Exception $e) {

            // Token expired, unset it.
            setcookie('session_token');

            return $next($req, $res);

        }
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Services\JWT\Decoder;
use Exception;

/**
 * Adds the "session" request attribute containing the user's session claims
 * if a session cookie is set.
 */
final class Session
{
    const TOKEN = 'session';

    /** @var \ThePetPark\Services\JWT\Decoder */
    private $decoder;

    public function __construct(Decoder $jwtDecoder)
    {
        $this->decoder = $jwtDecoder;
    }

    public function __invoke(Request $req, Response $res, callable $next)
    {
        if (isset($_COOKIE[self::TOKEN]) === false) {
            return $next($req, $res);
        }

        try {

            $data = $this->decoder->decode($_COOKIE[self::TOKEN]);
            $req = $req->withAttribute(self::TOKEN, $data);

        } catch (Exception $e) {

            // Token expired, unset it.
            setcookie(self::TOKEN);

        } 
            
        return $next($req, $res);
    }
}
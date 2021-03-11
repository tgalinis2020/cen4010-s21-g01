<?php

declare(strict_types=1);

namespace ThePetPark\Http;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * A dummy enpoint to make sure Slim works as intended.
 */
final class HelloWorld
{
    public function __invoke(Request $req, Response $res): Response
    {
        $name = $req->getAttribute('name', 'world');
        $body = $res->getBody();

        $body->write(sprintf('Hello, %s!', $name));

        return $res->withHeader('Content-Type', 'text/plain');
    }
}


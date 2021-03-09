<?php

declare(strict_types=1);

namespace ThePetPark\Auth\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class HelloWorldHandler
{
    public function __construct()
    {
        // stub
    }

    public function __invoke(Request $req, Response $res): Response
    {
        $name = $req->getAttribute('name', 'world');
        $body = $res->getBody();

        $body->write(sprintf('<h1>Hello, %s!</h1>', $name));

        return $res;
    }
}


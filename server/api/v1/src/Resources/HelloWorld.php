<?php

declare(strict_types=1);

namespace ThePetPark\Resources;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class HelloWorld
{
    public function __invoke(Request $req, Response $res): Response
    {
        $name = $req->getAttribute('name', 'world');
        $body = $res->getBody();

        $body->write(sprintf('<h1>Hello, %s!</h1>', $name));

        return $res;
    }
}


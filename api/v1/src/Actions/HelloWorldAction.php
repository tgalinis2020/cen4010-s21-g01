<?php

declare(strict_types=1);

namespace ThePetPark\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class HelloWorldAction
{
    public function __construct()
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $args = $request->getArguments();
        $body = $response->getBody();

        $body->write(sprintf('<h1>Hello, %s!</h1>', $args['name'] ?? 'world'));

        return $response;
    }
}

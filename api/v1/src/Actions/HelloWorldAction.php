<?php declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class HelloWorldAction
{
    public function __construct()
    {
    }

    public function __invoke(Request $req, Response $res, array $args): Response
    {
        $body = $res->getBody();

        $body->write('<h1>Hello, world!</h1>');

        return $res;
    }
}

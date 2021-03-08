<?php declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class FetchPostsAction
{
    public function __construct()
    {
        // initialize dependencies here
    }

    public function __invoke(Request $req, Response $res, array $args): Response
    {
        // TODO: get data from DB and optionally parse query params
        return $res->withJson(['data' => []);
    }
}

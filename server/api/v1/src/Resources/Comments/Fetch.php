<?php

declare(strict_types=1);

namespace ThePetPark\Resources\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class Fetch
{
    public function __construct()
    {
        // initialize dependencies here
    }

    public function __invoke(Request $req, Response $res): Response
    {
        // TODO: get data from DB and optionally parse query params
        return $res->withJson(['data' => []]);
    }
}


<?php

declare(strict_types=1);

namespace ThePetPark\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CreatePostAction
{
    public function __construct()
    {
        // initialize dependencies here
    }

    public function __invoke(Request $req, Response $res, array $args): Response
    {
        // TODO: read post info from request and save to DB
        return $res->withStatus(201);
    }
}

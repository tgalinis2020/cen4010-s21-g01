<?php

declare(strict_types=1);

namespace ThePetPark\Resources\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class CreateItem
{
    public function __construct()
    {
    }

    public function handle(Request $req, Response $res): Response
    {
        // TODO: read post info from request and save to DB
        return $res->withStatus(201);
    }
}


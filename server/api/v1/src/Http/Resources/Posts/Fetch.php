<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Posts;

use Slim\Http\Request;
use Slim\Http\Response;
use Doctrine\DBAL\Connection;

final class Fetch
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        // TODO: get data from DB and optionally parse query params
        return $res->withJson(['data' => []]);
    }
}


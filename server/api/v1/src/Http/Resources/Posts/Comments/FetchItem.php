<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Posts\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;

use function json_encode;

final class FetchItem
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
        $res->getBody()->write(json_encode(['data' => null]));

        return $res->withStatus(200);
    }
}


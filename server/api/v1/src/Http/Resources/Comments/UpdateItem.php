<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Comments;

use Slim\Http\Request;
use Slim\Http\Response;
use Doctrine\DBAL\Connection;

final class UpdateItem
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function handle(Request $req, Response $res): Response
    {
        // TODO: read post info from request and save to DB
        return $res->withStatus(201);
    }
}


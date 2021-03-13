<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Users;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
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
        return $res->withStatus(201);
    }
}


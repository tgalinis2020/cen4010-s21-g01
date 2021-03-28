<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph;

use function json_decode;

class Delete implements Graph\ActionInterface
{
    /** @var \Doctrine\DBAL\Connection */
    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }
    
    public function execute(Graph\App $graph, Request $req): Response
    {
        $res = $graph->createResponse();
        $docment = json_decode($req->getBody(), true);

        return $res->withStatus(501);
    }
}
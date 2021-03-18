<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Users;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Library\Graph;

use function json_decode;

class Update implements Graph\ActionInterface
{
    public function execute(Graph\App $graph, Request $req): Response
    {
        $res = $graph->createResponse();
        $conn = $graph->getConnection();
        $body = json_decode($req->getBody(), true);

        return $res->withStatus(501);
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Posts;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Library\Graph\ActionInterface;
use ThePetPark\Library\Graph\Graph;

use function json_decode;

class Delete implements ActionInterface
{
    public function execute(Graph $graph, Request $req, Response $res): Response
    {
        $conn = $graph->getConnection();
        $body = json_decode($req->getBody(), true);
        

        return $res->withStatus(501);
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ThePetPark\Library\Graph\ActionInterface;
use ThePetPark\Library\Graph\Graph;

class NotImplemented implements ActionInterface
{
    public function execute(
        Graph $graph,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        return $response->withStatus(501);
    }
}
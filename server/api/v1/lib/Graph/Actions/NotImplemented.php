<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Actions;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ThePetPark\Library\Graph;

class NotImplemented implements Graph\ActionInterface
{
    public function execute(
        Graph\App $graph,
        ServerRequestInterface $request
    ): ResponseInterface {
        return $graph->createResponse(501, 'Not Implemented');
    }
}
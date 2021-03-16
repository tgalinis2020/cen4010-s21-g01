<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action handlers map a HTTP verb to a schema action.
 */
interface ActionInterface
{
    public function execute(
        Graph $graph,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface;
}
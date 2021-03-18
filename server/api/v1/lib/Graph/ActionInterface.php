<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Graph defers handling any actions on resources to classes that implement
 * this interface.
 */
interface ActionInterface
{
    public function execute(App $graph, ServerRequestInterface $request): ResponseInterface;
}
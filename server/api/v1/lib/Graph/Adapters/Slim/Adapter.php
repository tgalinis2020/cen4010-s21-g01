<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Adapters\Slim;

use Slim;
use ThePetPark\Library\Graph;

use function array_keys;

/**
 * Slim 3 Adapter for Graph application.
 * Pass it as the second parameter of the Slim::group method.
 */
final class Adapter
{
    /** @var \ThePetPark\Library\Graph\App */
    private $graph;

    public function __construct(Graph\App $graph)
    {
        $this->graph = $graph;
    }

    public function __invoke(Slim\App $api)
    {
        $api->map(
            array_keys(Graph\App::SUPPORTED_METHODS),
            '/{graph_tok0}[/{graph_tok1}[/{graph_tok2}[/{graph_tok3}]]]',
            new Bootstrapper($this->graph)
        );
    }
}
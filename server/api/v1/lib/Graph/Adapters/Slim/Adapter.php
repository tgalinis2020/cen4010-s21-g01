<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Adapters\Slim;

use Slim;
use ThePetPark\Library\Graph;

/**
 * Slim 3 Adapter for Graph application.
 * Pass it as the second parameter of a call to Slim::group.
 */
final class Adapter
{
    /** @var \ThePetPark\Library\Graph\App */
    private $graph;

    /** @var string */
    private $idPattern;

    public function __construct(Graph\App $graph, string $idRegex = '[0-9]+')
    {
        $this->graph = $graph;
        $this->idPattern = $idRegex;
    }

    public function __invoke(Slim\App $api)
    {
        $api->map(
            Graph\App::SUPPORTED_METHODS,
            sprintf(
                '/{graph_tok0}[/{graph_tok1:%s}[/{graph_tok2}[/{graph_tok3}]]]',
                $this->idPattern
            ),
            new Bootstrapper($this->graph)
        );
    }
}
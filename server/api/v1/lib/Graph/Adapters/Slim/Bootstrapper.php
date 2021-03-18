<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Adapters\Slim;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ThePetPark\Library\Graph;

final class Bootstrapper
{
    /** @var \ThePetPark\Library\Graph\App */
    private $graph;

    public function __construct(Graph\App $graph)
    {
        $this->graph = $graph;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface {
        $tokens = [];
        $context = Graph\App::RESOURCE_CONTEXT;

        for ($i = 0; $i < 4; $i++) {
            $attr = 'graph_tok' . $i;
            $tokens[$i] = $request->getAttribute($attr);
            $request = $request->withoutAttribute($attr);
        }

        if ($tokens[2] === 'relationship') {
            $context = Graph\App::RELATIONSHIP_CONTEXT;
        }

        return $this->graph->handle(
            $request->withAttribute(Graph\App::PARAM_CONTEXT, $context)
        );
    }
}
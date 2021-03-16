<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Adapters;

use ThePetPark\Library\Graph\Graph;
use Slim\App;

/**
 * Maps Graph actions to Slim 3 application routes.
 * 
 * To use it, add the adapter to the application's dependency container and
 * mount the it as a route group.
 * 
 * Example:
 * $app->group('/api', Graph\Adapters\SlimAdapter::class);
 */
class SlimAdapter
{
    /** @var \ThePetPark\Library\Graph\Graph */
    private $graph;

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    public function __invoke(App $api)
    {
        $onRes    = [$this->graph, 'resolve'];
        $onMut    = [$this->graph, 'mutate'];
        $onResRel = [$this->graph, 'resolveRelationship'];
        $onMutRel = [$this->graph, 'mutateRelationship'];
        $res      = ['GET'];                                // resolve http verb
        $mut      = ['POST', 'PUT', 'PATCH', 'DELETE'];     // mutate http verbs
        $r        = [];                                     // route segments
        $r[0] =         '/{' . Graph::RESOURCE_TYPE. '}';
        $r[1] = $r[0] . '/{' . Graph::RESOURCE_ID . ':' . Graph::ID_REGEX. '}';
        $r[2] = $r[1] . '/{' . Graph::RELATIONSHIP_TYPE . '}';
        $r[3] = $r[1] . '/relationship' . '/{' . Graph::RELATIONSHIP_TYPE . '}';

        $api->map($res, $r[0], $onRes);    // resolve resource collection
        $api->map($mut, $r[0], $onMut);    // mutate resource collection
        $api->map($res, $r[1], $onRes);    // resolve resource item
        $api->map($mut, $r[1], $onMut);    // mutate resource item
        $api->map($res, $r[2], $onRes);    // resolve related resource
        $api->map($res, $r[3], $onResRel); // resolve related resource relationship
        $api->map($mut, $r[3], $onMutRel); // mutate related resource relationship

        // TODO: What about HEAD and OPTIONS requests?
    }
}
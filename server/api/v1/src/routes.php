<?php

declare(strict_types=1);

use ThePetPark\Resources;

/**
 * Map API endpoints to resource controllers.
 *
 * The provided resources must be defined in the app's dependency container.
 */
return function (Slim\App $app) {

    $app->group('/posts', function () {
        $this->map(['GET'],  '', Resources\Posts\Fetch::class);  
        $this->map(['POST'], '', Resources\Posts\CreateItem::class);

        $this->group('/{id}', function () {
            $this->map(['GET'],   '', Resources\Posts\FetchItem::class);
            $this->map(['PATCH'], '', Resources\Posts\UpdateItem::class);

            /*
            $this->group('/{relationship}', function () {
                $this->map(['GET'],    '', Resources\Post\Relationship\Fetch::class);
                $this->map(['POST'],   '', Resources\Post\Relationship\Create::class);
                $this->map(['PATCH'],  '', Resources\Post\Relationship\Update::class);
                $this->map(['DELETE'], '', Resources\Post\Relationship\Remove::class);
            });
            */
        });

        /*
        $this->group('/relationships/{relationship}', function () {
            $this->map(['GET'],    '', Resources\Post\Relationship\Fetch::class);
            $this->map(['POST'],   '', Resources\Post\Relationship\Create::class);
            $this->map(['PATCH'],  '', Resources\Post\Relationship\Update::class);
            $this->map(['DELETE'], '', Resources\Post\Relationship\Remove::class);
        });
         */
    });

    $app->map(['GET'], '/hello[/{name}]', Resources\HelloWorld::class);

};

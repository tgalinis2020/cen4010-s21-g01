<?php

declare(strict_types=1);

use ThePetPark\Resources;

/**
 * Map API endpoints to resource controllers.
 *
 * The provided resources must be defined in the app's dependency container.
 */
return function (Slim\App $api) {

    $api->group('/posts', function (Slim\App $posts) {
        $posts->map(['GET'],  '', Resources\Posts\Fetch::class);  
        $posts->map(['POST'], '', Resources\Posts\CreateItem::class);

        $posts->group('/{id}', function (Slim\App $post) {
            $post->map(['GET'],   '', Resources\Posts\FetchItem::class);
            $post->map(['PATCH'], '', Resources\Posts\UpdateItem::class);

            /*
            $post->group('/{relationship}', function (Slim\App $rel) {
                $rel->map(['GET'],    '', Resources\Post\Relationship\Fetch::class);
                $rel->map(['POST'],   '', Resources\Post\Relationship\Create::class);
                $rel->map(['PATCH'],  '', Resources\Post\Relationship\Update::class);
                $rel->map(['DELETE'], '', Resources\Post\Relationship\Remove::class);
            });
            */
        });

        /*
        $posts->group('/relationships/{relationship}', function (Slim\App $rel) {
            $rel->map(['GET'],    '', Resources\Post\Relationship\Fetch::class);
            $rel->map(['POST'],   '', Resources\Post\Relationship\Create::class);
            $rel->map(['PATCH'],  '', Resources\Post\Relationship\Update::class);
            $rel->map(['DELETE'], '', Resources\Post\Relationship\Remove::class);
        });
         */
    });

};

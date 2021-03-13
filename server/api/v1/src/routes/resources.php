<?php

declare(strict_types=1);

use ThePetPark\Http\Resources;

/**
 * Map API endpoints to resource controllers.
 *
 * The provided resources must be defined in the app's dependency container.
 */
return function (Slim\App $api) {

    $api->group('/users', function (Slim\App $users) {
        $users->map(['GET'],  '', Resources\Users\Fetch::class);
        $users->map(['POST'], '', Resources\Users\CreateItem::class);

        $users->group('/{user_id}', function (Slim\App $user) {
            $user->map(['GET'],   '', Resources\Users\FetchItem::class);
            $user->map(['PATCH'], '', Resources\Users\UpdateItem::class);

            $user->group('/subscriptions', function (Slim\App $subs) {

                $subs->group('/{pet_id}', function (Slim\App $pet) {

                });
            });

            $user->group('/favorites', function (Slim\App $favs) {

                $favs->group('/{post_id}', function (Slim\App $post) {

                });
            });
        });
    });


    $api->group('/posts', function (Slim\App $posts) {
        $posts->map(['GET'],  '', Resources\Posts\Fetch::class);  
        $posts->map(['POST'], '', Resources\Posts\CreateItem::class);

        $posts->group('/{post_id}', function (Slim\App $post) {
            $post->map(['GET'],   '', Resources\Posts\FetchItem::class);
            $post->map(['PATCH'], '', Resources\Posts\UpdateItem::class);

            $post->group('/comments', function (Slim\App $comments) {
                $comments->map(['GET'],  '', Resources\Posts\Comments\Fetch::class);
                $comments->map(['POST'], '', Resources\Posts\Comments\CreateItem::class);
                
                $comments->group('/{comment_id}', function (Slim\App $comment) {
                    $comment->map(['GET'],    '', Resources\Posts\Comments\FetchItem::class);
                    $comment->map(['PATCH'],  '', Resources\Posts\Comments\UpdateItem::class);
                    $comment->map(['DELETE'], '', Resources\Posts\Comments\DeleteItem::class);
                });
            });

            $post->group('/pets', function (Slim\App $pets) {

                $pets->group('/{pet_id}', function (Slim\App $pet) {

                });
            });

            $post->group('/tags', function (Slim\App $tags) {
                $tags->map(['POST'], '', Resources\Posts\Tags\CreateItem::class);

                $tags->group('/{tag_id}', function (Slim\App $tag) {

                });
            });
        });
    });


    $api->group('/pets', function (Slim\App $pets) {
        $pets->map(['GET'],  '', Resources\Pets\Fetch::class);
        $pets->map(['POST'], '', Resources\Pets\CreateItem::class);
    });

};

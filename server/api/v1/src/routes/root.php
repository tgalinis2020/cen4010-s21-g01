<?php

declare(strict_types=1);

use ThePetPark\Library\Graph\Adapters\SlimAdapter;

/**
 * Root endpoint-to-controller mappings.
 */
return function (Slim\App $app) {

    // Mount the API's resources to the root of the application.
    (require __DIR__ . '/resources.php')($app);

    // Mount the authentication functions to the session namespace.
    $app->group('/session', (require __DIR__ . '/session.php'));

    $app->map(['POST'], '/upload', ThePetPark\Http\UploadFile::class);
    
    //$app->map(['GET'], '/search', ThePetPark\Http\Search::class);
    
    // Dummy endpoint to make sure Slim works. Can be removed later.
    $app->map(['GET'], '/hello[/{name}]', ThePetPark\Http\HelloWorld::class);

    /*
    $app->group('/{resource_type}', function (Slim\App $resources) {

        $resources->get('',   Resources\Fetch::class);
        $resources->post('',  Resources\CreateItem::class);
        $resources->patch('', Resources\UpdateItem::class);
        
        $resources->group('/{resource_id:[0-9]+}', function (Slim\App $resource) {
            $resource->get('', Resources\FetchItem::class);
            
            $resource->group('/relationship/{related_id:[0-9]+}', function (Slim\App $relationship) {
                $relationship->get('', Resources\FetchRelationship::class);
                $relationship->post('', Resources\CreateRelationship::class);
            });

            $resource->get('/{related_id:[0-9]+}', Resources\FetchRelated::class);
        });
    });
    
    $app->group('/{resource_type}', function (Slim\App $resources) {

        $resources->get('[/{resource_id}]', '');
    });*/

};

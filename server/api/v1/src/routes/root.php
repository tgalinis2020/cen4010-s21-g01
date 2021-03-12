<?php

declare(strict_types=1);

/**
 * Root endpoint-to-controller mappings.
 */
return function (Slim\App $app) {

    // Mount the API's resources to the root of the application.
    (require __DIR__ . '/resources.php')($app);

    // Mount the authentication functions to the session namespace.
    $app->group('/session', require __DIR__ . '/session.php');

    $app->map(['POST'], '/upload', ThePetPark\Http\UploadFile::class);
    
    $app->map(['GET'], '/search', ThePetPark\Http\Search::class);
    
    // Dummy endpoint to make sure Slim works. Can be removed later.
    $app->map(['GET'], '/hello[/{name}]', ThePetPark\Http\HelloWorld::class);

};

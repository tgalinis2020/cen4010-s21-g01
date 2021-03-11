<?php

declare(strict_types=1);

/**
 * Root endpoint-to-controller mappings.
 */
return function (Slim\App $app) {

    // Mount the API's resources to the root of the application.
    $app->group('', require __DIR__ . '/resources.php');

    // Mount the authentication functions to the auth namespace.
    $app->group('/auth', require __DIR__ . '/auth.php');

    $app->map(['POST'], '/upload', ThePetPark\Http\UploadFile::class);
    
    $app->map(['GET'], '/search', ThePetPark\Http\Search::class);
    
    $app->map(['GET'], '/hello[/{name}]', ThePetPark\Http\HelloWorld::class);

};

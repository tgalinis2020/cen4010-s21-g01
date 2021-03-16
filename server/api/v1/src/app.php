<?php

declare(strict_types=1);

use ThePetPark\Library\Graph;

date_default_timezone_set('UTC');

require __DIR__ . '/../vendor/autoload.php';

return (function () {
    $builder = new DI\ContainerBuilder();

    $builder->addDefinitions(__DIR__ . '/def/settings.php');
    $builder->addDefinitions(__DIR__ . '/def/slim.php');
    $builder->addDefinitions(__DIR__ . '/def/services.php');
    $builder->addDefinitions(__DIR__ . '/def/http.php');
    //$builder->addDefinitions(__DIR__ . '/def/resources.php');
    $builder->addDefinitions(__DIR__ . '/def/middleware.php');

    $app = new Slim\App($builder->build());

    $app->add(ThePetPark\Middleware\SessionMiddleware::class);

    // Mount the authentication functions to the session namespace.
    $app->group('/session', (require __DIR__ . '/session.php'));

    $app->map(['POST'], '/upload', ThePetPark\Http\UploadFile::class);
    
    //$app->map(['GET'], '/search', ThePetPark\Http\Search::class);
    
    // Dummy endpoint to make sure Slim works. Can be removed later.
    $app->map(['GET'], '/hello[/{name}]', ThePetPark\Http\HelloWorld::class);

    // Mount the resource graph.
    $app->group('', Graph\Adapters\SlimAdapter::class);

    return $app;
})();


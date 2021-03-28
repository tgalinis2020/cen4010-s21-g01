<?php

declare(strict_types=1);

use ThePetPark\Library\Graph;

date_default_timezone_set('UTC');

return (function () {
    $root = dirname(__DIR__);
    
    require $root . '/vendor/autoload.php';

    $app = new Slim\App((new DI\ContainerBuilder())
        ->enableCompilation($root . '/var/cache')
        ->addDefinitions($root . '/etc/settings.php')
        ->addDefinitions($root . '/etc/slim.php')
        ->addDefinitions($root . '/etc/definitions.php')
        ->build()
    );

    // The Session middleware reads the session cookie and attaches the user's
    // session details to the request.
    $app->add(ThePetPark\Middleware\Session::class);

    // Mount the authentication functions to the session namespace.
    $app->group('/session', function (Slim\App $session) {
        $session->map(['GET'],    '', ThePetPark\Http\Session\Resolve::class);
        $session->map(['POST'],   '', ThePetPark\Http\Session\Create::class);
        $session->map(['DELETE'], '', ThePetPark\Http\Session\Delete::class);
    });

    $app->map(['POST'], '/upload', ThePetPark\Http\UploadFile::class);

    // Mount the resource graph.
    $app->group('', Graph\Adapters\Slim\Adapter::class);

    return $app;
})();


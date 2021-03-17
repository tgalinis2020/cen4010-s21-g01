<?php

declare(strict_types=1);

use ThePetPark\Library\Graph\Graph;

date_default_timezone_set('UTC');

require __DIR__ . '/../vendor/autoload.php';

return (function () {
    $builder = new DI\ContainerBuilder();

    $builder->addDefinitions(__DIR__ . '/../etc/settings.php');
    $builder->addDefinitions(__DIR__ . '/../etc/slim.php');
    $builder->addDefinitions(__DIR__ . '/../etc/definitions.php');

    $app = new Slim\App($builder->build());

    $app->add(ThePetPark\Middleware\SessionMiddleware::class);

    // Mount the authentication functions to the session namespace.
    $app->group('/session', function (Slim\App $session) {
        $session->map(['GET'],    '', ThePetPark\Http\Session\Resolve::class);
        $session->map(['POST'],   '', ThePetPark\Http\Session\Create::class);
        $session->map(['DELETE'], '', ThePetPark\Http\Session\Delete::class);
    });

    $app->map(['POST'], '/upload', ThePetPark\Http\UploadFile::class);

    // Mount the resource graph.
    $app->map(
        ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
        '/{' . Graph::TOKENS . ':.+}',
        Graph::class . ':resolve'
    );

    return $app;
})();


<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

require __DIR__ . '/../vendor/autoload.php';

return (function () {
    $builder = new DI\ContainerBuilder();

    $builder->addDefinitions(__DIR__ . '/def/settings.php');
    $builder->addDefinitions(__DIR__ . '/def/slim.php');
    $builder->addDefinitions(__DIR__ . '/def/services.php');
    $builder->addDefinitions(__DIR__ . '/def/http.php');
    $builder->addDefinitions(__DIR__ . '/def/resources.php');
    $builder->addDefinitions(__DIR__ . '/def/middleware.php');

    $app = new Slim\App($builder->build());

    $app->add(ThePetPark\Middleware\SessionMiddleware::class);

    (require __DIR__ . '/routes/root.php')($app);

    return $app;
})();


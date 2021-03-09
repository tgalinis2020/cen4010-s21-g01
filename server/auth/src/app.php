<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

return (function () {
    $builder = new DI\ContainerBuilder();

    // Bootstrap the container.
    $builder->addDefinitions(__DIR__ . '/../../common/def/settings.php');
    $builder->addDefinitions(__DIR__ . '/def/renderer.php');
    $builder->addDefinitions(__DIR__ . '/def/handlers.php');
    $builder->addDefinitions(__DIR__ . '/def/middleware.php');
    $builder->addDefinitions(__DIR__ . '/def/services.php');

    // Uncomment if definition cache is available.
    // The app doesn't have write permissions on FAU's LAMP server outside
    // of public_html so the cache cannot be created on-the-fly.
    // It must be generated in another machine and pushed into the git repo.
    //$builder->enableCompilation(__DIR__ . '/../var/cache');

    $app = new Slim\App($builder->build());

    (require __DIR__ . '/middleware.php')($app);
    (require __DIR__ . '/routes.php')($app);

    return $app;
})();


<?php

declare(strict_types=1);

/**
 * Bootstrap the dependency container.
 */
return function (DI\ContainerBuilder $builder) {
    $builder->addDefinitions(__DIR__ . '/def/settings.php');
    $builder->addDefinitions(__DIR__ . '/def/slim.php');
    $builder->addDefinitions(__DIR__ . '/def/services.php');
    $builder->addDefinitions(__DIR__ . '/def/http.php');
    $builder->addDefinitions(__DIR__ . '/def/resources.php');
    $builder->addDefinitions(__DIR__ . '/def/middleware.php');
};

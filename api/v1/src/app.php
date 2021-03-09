<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$builder = new \DI\ContainerBuilder();

$builder->addDefinitions(__DIR__ . '/../vendor/php-di/slim-bridge/src/config.php');
$builder->addDefinitions(__DIR__ . '/actions.def.php');
$builder->addDefinitions(__DIR__ . '/services.def.php');

// Uncomment if definition cache is available.
// The app doesn't have write permissions on FAU's LAMP server outside
// of public_html so the cache cannot be created on-the-fly.
//$container->enableCompilation(__DIR__ . '/../var/cache');

$app = new \Slim\App($builder->build());

(require __DIR__ . '/middleware.php')($app);
(require __DIR__ . '/routes.php')($app);

return $app;

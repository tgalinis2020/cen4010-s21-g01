<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

require __DIR__ . '/../vendor/autoload.php';

return (function () {
    $builder = new DI\ContainerBuilder();

    (require __DIR__ . '/definitions.php')($builder);

    $app = new Slim\App($builder->build());

    (require __DIR__ . '/middleware.php')($app);
    (require __DIR__ . '/routes.php')($app);

    return $app;
})();


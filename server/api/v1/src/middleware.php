<?php

declare(strict_types=1);

use ThePetPark\Middleware\SessionMiddleware;

return function (Slim\App $app) {
    $app->add(SessionMiddleware::class);
};

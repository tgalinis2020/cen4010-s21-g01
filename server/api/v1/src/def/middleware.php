<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Middleware\SessionMiddleware;
use ThePetPark\Services;

use function DI\factory;

return [
    
    SessionMiddleware::class => factory(function (ContainerInterface $c) {
        return new SessionMiddleware(
            $c->get(Services\JWT\Encoder::class)
        );
    }),

];

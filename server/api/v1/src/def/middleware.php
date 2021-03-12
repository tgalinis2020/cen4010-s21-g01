<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Middleware\SessionMiddleware;
use ThePEtPark\Middleware\JsonDataMiddleware;

use function DI\factory;

return [
    
    SessionMiddleware::class => factory(function (ContainerInterface $c) {
        return new SessionMiddleware($c->get('jwt_decoder'));
    }),

];

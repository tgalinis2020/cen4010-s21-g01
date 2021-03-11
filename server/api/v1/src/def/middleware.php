<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Middleware\SessionMiddleware;
use ThePEtPark\Middleware\JsonDataMiddleware;

use function DI\factory;

return [
    
    SessionMiddleware::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['firebase']['php-jwt'];

        return new SessionMiddleware(
            $settings['secret_key'],
            $settings['allowed_algs']
        );
    }),

];

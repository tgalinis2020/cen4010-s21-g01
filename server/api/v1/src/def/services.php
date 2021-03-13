<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL;
use ThePetPark\Services;
use function DI\factory;

/**
 * Any dependencies that are not provided by Slim (other than Controllers)
 * should be listed here.
 */
return [

    DBAL\Connection::class => factory(function (ContainerInterface $c) {
        return DBAL\DriverManager::getConnection(
            $c->get('settings')['doctrine']['connection']
        );
    }),

    Services\JWT\Encoder::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['firebase']['php-jwt'];

        return new Services\JWT\Encoder(
            $settings['secret_key'],
            $settings['algorithms'][$settings['selected_algorithm']]
        );
    }),

    Services\JWT\Decoder::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['firebase']['php-jwt'];

        return new Services\JWT\Decoder(
            $settings['secret_key'],
            $settings['algorithms']
        );
    }),

];

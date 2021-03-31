<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL;
use ThePetPark\Services;
use ThePetPark\Schema;

use function DI\factory;

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

    Schema\Container::class => factory(function (ContainerInterface $c) {
        $definitions = require $c->get('settings')['definitions'];
        $schemas = [];

        foreach ($definitions as $definition) { 
            $schema = Schema::fromArray($definition);
            $schemas[$schema->getType()] = $schema;
        }

        return new Schema\Container($schemas);
    }),

];

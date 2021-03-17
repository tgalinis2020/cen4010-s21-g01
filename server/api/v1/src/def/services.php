<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL;
use ThePetPark\Services;
use ThePetPark\Library\Graph\Graph;

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

    Graph::class => factory(function (ContainerInterface $c) {
        $graph = new Graph(
            $c->get(DBAL\Connection::class),
            $c->get('settings')['graph']
        );

        // This app's actions don't have dependencies outside of Doctrine
        // so a container is not necessary.
        //$graph->setContainer($c);

        return $graph;
    }),

];

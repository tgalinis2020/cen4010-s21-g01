<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL;
use ThePetPark\Services;
use ThePetPark\Models;
use ThePetPark\Library\Graph\Graph;
use ThePetPark\Library\Graph\Adapters\SlimAdapter;

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

    SlimAdapter::class => factory(function (ContainerInterface $c) {
        $graph = new Graph($c->get(DBAL\Connection::class));

        $graph->add(new Models\Users());
        $graph->add(new Models\Pets());
        $graph->add(new Models\Pets\Breeds());
        $graph->add(new Models\Pets\Types());
        $graph->add(new Models\Posts());
        $graph->add(new Models\Comments());
        $graph->add(new Models\Tags());

        return $graph;
    }),

];

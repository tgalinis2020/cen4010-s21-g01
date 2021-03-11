<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL;

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

];

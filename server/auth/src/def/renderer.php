<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\Views;
use Slim\Http\Uri;

return [
    Slim\Views\Twig::class => function (ContainerInterface $c) {
        $renderer = new Views\Twig(__DIR__ . '/../templates', [
            'cache' => false,
        ]);
        
        // Instantiate and add Slim specific extension
        $router = $c->get('router');
        $uri = Uri::createFromEnvironment($c->get('environment'));

        $renderer->addExtension(new Views\TwigExtension($router, $uri));

        return $renderer;
    },
];


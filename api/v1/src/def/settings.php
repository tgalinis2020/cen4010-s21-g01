<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Handlers\Strategies;

use function DI\create;
use function DI\get;

return [
    'settings.httpVersion' => '1.1',
    'settings.responseChunkSize' => 4096,
    'settings.outputBuffering' => 'append',
    'settings.determineRouteBeforeAppMiddleware' => false,
    'settings.displayErrorDetails' => true,
    'settings.addContentLengthHeader' => true,
    'settings.routerCacheFile' => false,

    'settings' => [
        'httpVersion' => get('settings.httpVersion'),
        'responseChunkSize' => get('settings.responseChunkSize'),
        'outputBuffering' => get('settings.outputBuffering'),
        'determineRouteBeforeAppMiddleware' => get('settings.determineRouteBeforeAppMiddleware'),
        'displayErrorDetails' => get('settings.displayErrorDetails'),
        'addContentLengthHeader' => get('settings.addContentLengthHeader'),
        'routerCacheFile' => get('settings.routerCacheFile'),
    ],

    // Default Slim services
    'router' => create(Slim\Router::class)
        ->method('setContainer', get(ContainerInterface::class))
        ->method('setCacheFile', get('settings.routerCacheFile')),

    'errorHandler' => create(Slim\Handlers\Error::class)
        ->constructor(get('settings.displayErrorDetails')),

    'phpErrorHandler' => create(Slim\Handlers\PhpError::class)
        ->constructor(get('settings.displayErrorDetails')),

    'notFoundHandler' => create(Slim\Handlers\NotFound::class),

    'notAllowedHandler' => create(Slim\Handlers\NotAllowed::class),

    'environment' => create(Slim\Http\Environment::class)
        ->constructor($_SERVER),

    'request' => function (ContainerInterface $c) {
        return Request::createFromEnvironment($c->get('environment'));
    },

    'response' => function (ContainerInterface $c) {
        $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
        $response = new Response(200, $headers);
        return $response->withProtocolVersion($c->get('settings')['httpVersion']);
    },

    'foundHandler' => create(Strategies\RequestResponse::class),

    'callableResolver' => create(Slim\CallableResolver::class)
        ->constructor(get(ContainerInterface::class)),

    // Aliases
    ContainerInterface::class => get(DI\Container::class),
    Slim\Router::class => get('router'),
];

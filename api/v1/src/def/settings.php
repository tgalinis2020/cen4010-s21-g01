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
    'settings.displayErrorDetails' => false,
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
        ->method('setContainer', get(DI\Container::class))
        ->method('setCacheFile', get('settings.routerCacheFile')),

    'errorHandler' => create(Slim\Handlers\Error::class)
        ->constructor(get('settings.displayErrorDetails')),

    'phpErrorHandler' => create(Slim\Handlers\PhpError::class)
        ->constructor(get('settings.displayErrorDetails')),

    'notFoundHandler' => create(Slim\Handlers\NotFound::class),

    'notAllowedHandler' => create(Slim\Handlers\NotAllowed::class),

    'environment' => function (ContainerInterface $c) {
        return new Slim\Http\Environment($_SERVER);
    },

    'request' => function (ContainerInterface $c) {
        return Request::createFromEnvironment($c->get('environment'));
    },

    'response' => function (ContainerInterface $c) {
        $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
        $response = new Response(200, $headers);
        return $response->withProtocolVersion($c->get('settings')['httpVersion']);
    },

    /*
    'foundHandler' => DI\create(ControllerInvoker::class)
        ->constructor(DI\get('foundHandler.invoker')),
    'foundHandler.invoker' => function (ContainerInterface $c) {
        $resolvers = [
            // Inject parameters by name first
            new AssociativeArrayResolver,
            // Then inject services by type-hints for those that weren't resolved
            new TypeHintContainerResolver($c),
            // Then fall back on parameters default values for optional route parameters
            new DefaultValueResolver(),
        ];
        return new Invoker(new ResolverChain($resolvers), $c);
    },

    'callableResolver' => \DI\create(CallableResolver::class),
     */

    'foundHandler' => function (ContainerInterface $c) {
        return new Slim\Handlers\Strategies\RequestResponseArgs();
    },

    'callableResolver' => function (ContainerInterface $c) {
        return new Slim\CallableResolver($c);
    },

    // Aliases
    ContainerInterface::class => get(DI\Container::class),
    Slim\Router::class => get('router'),
];

<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Handlers\Strategies;

return [
    'settings.httpVersion' => '1.1',
    'settings.responseChunkSize' => 4096,
    'settings.outputBuffering' => 'append',
    'settings.determineRouteBeforeAppMiddleware' => false,
    'settings.displayErrorDetails' => false,
    'settings.addContentLengthHeader' => true,
    'settings.routerCacheFile' => false,

    'settings' => [
        'httpVersion' => DI\get('settings.httpVersion'),
        'responseChunkSize' => DI\get('settings.responseChunkSize'),
        'outputBuffering' => DI\get('settings.outputBuffering'),
        'determineRouteBeforeAppMiddleware' => DI\get('settings.determineRouteBeforeAppMiddleware'),
        'displayErrorDetails' => DI\get('settings.displayErrorDetails'),
        'addContentLengthHeader' => DI\get('settings.addContentLengthHeader'),
        'routerCacheFile' => DI\get('settings.routerCacheFile'),
    ],

    // Default Slim services
    'router' => DI\object(Slim\Router::class)
        ->method('setContainer', DI\get(Container::class))
        ->method('setCacheFile', DI\get('settings.routerCacheFile')),

    'errorHandler' => DI\object(Slim\Handlers\Error::class)
        ->constructor(DI\get('settings.displayErrorDetails')),

    'phpErrorHandler' => DI\object(Slim\Handlers\PhpError::class)
        ->constructor(DI\get('settings.displayErrorDetails')),

    'notFoundHandler' => DI\object(Slim\Handlers\NotFound::class),

    'notAllowedHandler' => DI\object(Slim\Handlers\NotAllowed::class),

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
    'foundHandler' => DI\object(ControllerInvoker::class)
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

    'callableResolver' => \DI\object(CallableResolver::class),
     */

    'foundHandler' => function (ContainerInterface $c) {
        return new Slim\Handlers\Strategies\RequestResponseArgs();
    },

    'callableResolver' => function (ContainerInterface $c) {
        return new Slim\CallableResolver($c);
    },

    // Aliases
    ContainerInterface::class => DI\get(Container::class),
    Slim\Router::class => DI\get('router'),
];

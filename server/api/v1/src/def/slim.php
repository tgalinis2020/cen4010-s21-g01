<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

use function DI\factory;

/**
 * Since we're not using Slim 3's default container, the following keys must
 * be defined in order for the application work properly.
 * 
 * Used Slim 3's DefaultSettingsProvider for reference.
 * https://github.com/slimphp/Slim/blob/3.x/Slim/DefaultServicesProvider.php
 */
return [

    'router' => factory(function (ContainerInterface $c) {
        $router = new Slim\Router();

        $router->setContainer($c);
        $router->setCacheFile($c->get('settings')['routerCacheFile']);

        return $router;
    }),

    'environment' => factory(function (ContainerInterface $c) {
        return new Slim\Http\Environment($_SERVER);
    }),
    
    'request' => factory(function (ContainerInterface $c) {
        return Slim\Http\Request::createFromEnvironment($c->get('environment'));
    }),

    'response' => factory(function (ContainerInterface $c) {
        $headers = new Slim\Http\Headers([
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
        $response = new Slim\Http\Response(200, $headers);

        return $response->withProtocolVersion($c->get('settings')['httpVersion']);
    }),

    'foundHandler' => factory(function (ContainerInterface $c) {
        return new Slim\Handlers\Strategies\RequestResponse();
    }),

    'callableResolver' => factory(function (ContainerInterface $c) {
        return new Slim\CallableResolver($c);
    }),

    'errorHandler' => factory(function (ContainerInterface $c) {
        return new Slim\Handlers\Error($c->get('settings')['displayErrorDetails']);
    }),

    'phpErrorHandler' => factory(function (ContainerInterface $c) {
        return new Slim\Handlers\PhpError($c->get('settings')['displayErrorDetails']);
    }),

    'notFoundHandler' => factory(function (ContainerInterface $c) {
        return new Slim\Handlers\NotFound();
    }),

    'notAllowedHandler' => factory(function (ContainerInterface $c) {
        return new Slim\Handlers\NotAllowed();
    }),

];

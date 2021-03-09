<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Handlers\Strategies;
use Doctrine\DBAL;

use function DI\create;
use function DI\get;

/**
 * Default Slim settings and services used by both
 * the authentication app and API.
 *
 * This may beg the question: why keep both components separate?
 * Answer: normally different components of a project can be deployed
 * on different machines (ex. Google has a app dedicated to auth that
 * other Google services use to authenticate a user).
 *
 * Keeping a nice separation of concerns makes a project more scaleable!
 */
return [

    'settings' => [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true,
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ],

    'router' => function (ContainerInterface $c) {
        $router = new Slim\Router();

        $router->setContainer($c->get(ContainerInterface::class));
        $router->setCacheFile($c->get('settings')['routerCacheFile']);

        return $router;
    },

    'errorHandler' => function (ContainerInterface $c) {
        return new Slim\Handlers\Error($c->get('settings')['displayErrorDetails']);
    },

    'phpErrorHandler' => function (ContainerInterface $c) {
        return new Slim\Handlers\PhpError($c->get('settings')['displayErrorDetails']);
    },

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

    DBAL\Connection::class => function (ContainerInterface $c) {
        
        // Kind of a hacky way to leave DB credentials out of
        // version control, but it's necessary since it is not
        // possible to set environment variables and/or read
        // files outside of ~/public_html in FAU's LAMP server.
        // Kind of annoying since ~/.my.cnf conveniently contains
        // the username and password.
        //
        // In the project's server directory, a file called "credentials.php"
        // must exist (and be ignored by Git). The file must return
        // an array containing two strings: the MySQL user account
        // and password.
        list($user, $passwd) = require __DIR__ . '/../credentials.php';

        return DBAL\DriverManager::getConnection(
            sprintf("mysql://%s:%s@127.0.0.1/thepetpark", $user, $passwd)
        );

    },

];

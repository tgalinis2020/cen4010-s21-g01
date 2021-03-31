<?php

declare(strict_types=1);

use ThePetPark\Http;
use ThePetPark\Middleware\Guard;
use ThePetPark\Middleware\Features;

date_default_timezone_set('UTC');

return (function () {
    $root = dirname(__DIR__);
    
    require $root . '/vendor/autoload.php';

    $app = new Slim\App(
        (new \DI\ContainerBuilder())
            ->enableCompilation($root . '/var/cache')
            ->addDefinitions($root . '/etc/settings.php')
            ->addDefinitions($root . '/etc/slim.php')
            ->addDefinitions($root . '/etc/services.php')
            ->addDefinitions($root . '/etc/middleware.php')
            ->addDefinitions($root . '/etc/actions.php')
            ->build()
    );

    // Since multiple API endpoints can resolve to resources, this function
    // helps bootstrap them. Add/remove feature middleware as needed.
    //
    // Note that Slim puts middleware in a stack so the last middleware added is
    // the first one to execute.
    function addResolver(string $pattern, Slim\App $route) {
        $route->get($pattern, Http\Actions\Resolve::class)
            ->add(Features\Pagination\Links\PageBased::class)
            ->add(Features\Pagination\Links\OffsetBased::class)
            ->add(Features\Pagination\Links\CursorBased::class)
            ->add(Features\ParseIncludes::class)
            ->add(Features\Resolver::class)        // This is the main feature.
            ->add(Features\Pagination\PageBased::class)
            ->add(Features\Pagination\OffsetBased::class)
            ->add(Features\Pagination\CursorBased::class)
            ->add(Features\Sorting::class)
            ->add(Features\Filtering::class)
            ->add(Features\SparseFieldsets::class)
            ->add(Features\Initialization::class); // This must be invoked first!
    }

    // The Session middleware reads the session cookie and attaches the user's
    // session details to the request.
    $app->add(ThePetPark\Middleware\Session::class);

    // Images must be uploaded before a post can be created.
    $app->post('/upload', ThePetPark\Http\UploadFile::class);

    // Mount the authentication functions to the session namespace.
    // Sessions are resources that are managed by client applications, not this
    // server. The server simply validates them.
    $app->group('/session', function (Slim\App $session) {
        $session->get('', ThePetPark\Http\Session\Resolve::class)->add(Guard::class);
        $session->post('', ThePetPark\Http\Session\Create::class);
        $session->delete('', ThePetPark\Http\Session\Delete::class)->add(Guard::class);
    });

    // Native user accounts have passwords. Since authentication actions require
    // a bit more granularity, they must be handled manually.
    $app->group('/passwords/{id}', function (Slim\App $passwd) {
        $passwd->put('', ThePetPark\Http\Passwords\Set::class);
        $passwd->patch('', ThePetPark\Http\Passwords\Update::class);
    });
    
    // Resource graph action-to-route mappings.
    // The order routes are defined matters: more specific routes should be
    // declared first (like the ones above here).
    $app->group('/{resource:[A-Za-z-]+}', function (Slim\App $r) {
        addResolver('', $r);
        $r->post('', Http\Actions\Add::class)->add(Guard::class);

        $r->group('/{id:[0-9]+}', function (Slim\App $s) {
            addResolver('', $s);
            $s->patch('',   Http\Actions\Update::class)->add(Guard::class);
            $s->delete('',  Http\Actions\Remove::class)->add(Guard::class);
            addResolver('/{relationship:(?!relationships)[A-Za-z-]+}', $s);

            $s->group('/relationships/{relationship:[A-Za-z-]+}', function (Slim\App $t) {
                //addResolver('', $t); // not supported
                $t->post('',   Http\Actions\Relationships\Add::class)->add(Guard::class);    // add for to-many, no-op for to-one
                $t->patch('',  Http\Actions\Relationships\Update::class)->add(Guard::class); // replace for to-many, add+remove for to-one
                $t->delete('', Http\Actions\Relationships\Remove::class)->add(Guard::class); // remove for to-many, no-op for to-one
            });            
        });
    })->add(Features\Check::class);

    return $app;
})();


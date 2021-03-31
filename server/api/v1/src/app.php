<?php

declare(strict_types=1);

namespace ThePetPark;

use Slim;

date_default_timezone_set('UTC');

return (function () {
    $root = dirname(__DIR__);
    
    require $root . '/vendor/autoload.php';

    $app = new Slim\App(
        (new \DI\ContainerBuilder())
            //->enableCompilation($root . '/var/cache')
            ->addDefinitions($root . '/etc/settings.php')
            ->addDefinitions($root . '/etc/slim.php')
            ->addDefinitions($root . '/etc/actions.php')
            ->addDefinitions($root . '/etc/middleware.php')
            ->addDefinitions($root . '/etc/services.php')
            ->build()
    );

    // Since multiple API endpoints can resolve to resources, this function
    // helps bootstrap them. Add/remove feature middleware as needed.
    //
    // Note that Slim puts middleware in a stack so the last middleware added is
    // the first one to execute.
    function addResolver(Slim\App $endpoint, string $pattern) {
        $endpoint->get($pattern, Http\Actions\Resolve::class)
            ->add(Middleware\Features\Pagination\Links\PageBased::class)
            ->add(Middleware\Features\Pagination\Links\OffsetBased::class)
            ->add(Middleware\Features\Pagination\Links\CursorBased::class)
            ->add(Middleware\Features\ParseIncludes::class)
            ->add(Middleware\Features\Resolver::class)        // This is the main feature.
            ->add(Middleware\Features\Pagination\PageBased::class)
            ->add(Middleware\Features\Pagination\OffsetBased::class)
            ->add(Middleware\Features\Pagination\CursorBased::class)
            ->add(Middleware\Features\Sorting::class)
            ->add(Middleware\Features\Filtering::class)
            ->add(Middleware\Features\SparseFieldsets::class)
            ->add(Middleware\Features\Initialization::class); // This must be invoked first!
    }

    // The "Protect" middleware ensures the user owns the resource they are
    // trying to mutate.
    function protect(Slim\Route $route) {
        $route
            ->add(Middleware\Auth\Protect::class)
            ->add(Middleware\Auth\Guard::class);
    }

    // The Session middleware reads the session cookie and attaches the user's
    // session details to the request.
    $app->add(Middleware\Auth\Session::class);

    // Images must be uploaded before a post can be created.
    $app->post('/upload', ThePetPark\Http\UploadFile::class);

    // Mount the authentication functions to the session namespace.
    // Sessions are resources that are managed by client applications, not this
    // server. The server simply validates them.
    $app->group('/session', function (Slim\App $session) {
        $session->get('', Http\Session\Resolve::class);
        $session->post('', Http\Session\Create::class);
        $session->delete('', Http\Session\Delete::class);
    });

    // Native user accounts have passwords. Since authentication actions require
    // a bit more granularity, they must be handled manually.
    $app->group('/passwords/{id}', function (Slim\App $passwd) {
        $passwd->put('', Http\Passwords\Set::class);
        $passwd->patch('', Http\Passwords\Update::class);
    });
    
    // Resource graph action-to-route mappings.
    // The order routes are defined matters: more specific routes should be
    // declared first (like the ones above here).
    //
    // The "Check" middleware ensures that the requested resource exists before
    // attempting to do any operations.
    $app->group('/{resource:[A-Za-z-]+}', function (Slim\App $r) {
        addResolver($r, '');

        $r->post('', Http\Actions\Add::class)
            ->add(Middleware\Features\Auth\Guard\Permissive::class);

        $r->group('/{id:[0-9]+}', function (Slim\App $s) {
            addResolver($s, '');
            protect($s->patch('',   Http\Actions\Update::class));
            protect($s->delete('',  Http\Actions\Remove::class));
            addResolver($s, '/{relationship:(?!relationships)[A-Za-z-]+}');

            $s->group('/relationships/{relationship:[A-Za-z-]+}', function (Slim\App $t) {
                //addResolver($t, '');                                             // not supported
                protect($t->post('',   Http\Actions\Relationships\Add::class));    // add for to-many, no-op for to-one
                protect($t->patch('',  Http\Actions\Relationships\Update::class)); // replace for to-many (not supported), add+remove for to-one
                protect($t->delete('', Http\Actions\Relationships\Remove::class)); // remove for to-many, no-op for to-one
            });            
        });
    })->add(Middleware\Features\Check::class);

    return $app;
})();


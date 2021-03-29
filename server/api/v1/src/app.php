<?php

declare(strict_types=1);

use ThePetPark\Http;
use ThePetPark\Middleware\Guard;

date_default_timezone_set('UTC');

return (function () {
    $root = dirname(__DIR__);
    
    require $root . '/vendor/autoload.php';
    require $root . '/var/cache/CompiledContainer.php';

    $app = new Slim\App(new CompiledContainer());

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
        $r->get('',  Http\Actions\Resolve::class);
        $r->post('', Http\Actions\Add::class)->add(Guard::class);

        $r->group('/{id:[0-9]+}', function (Slim\App $s) {
            $s->get('',     Http\Actions\Resolve::class);
            $s->patch('',   Http\Actions\Update::class)->add(Guard::class);
            $s->delete('',  Http\Actions\Remove::class)->add(Guard::class);
            $s->get('/{relationship:(?!relationships)[A-Za-z-]+}', Http\Actions\Resolve::class);

            $s->group('/relationships/{relationship:[A-Za-z-]+}', function (Slim\App $t) {
                //$t->get('', Http\Actions\Relationships\Resolve::class); // not implemented
                $t->post('',   Http\Actions\Relationships\Add::class)->add(Guard::class);    // add for to-many, no-op for to-one
                $t->patch('',  Http\Actions\Relationships\Update::class)->add(Guard::class); // replace for to-many, add+remove for to-one
                $t->delete('', Http\Actions\Relationships\Remove::class)->add(Guard::class); // remove for to-many, no-op for to-one
            });            
        });
    });

    // TODO:    To-one relationships are updated using PATCH requests.
    //          To-many relationships are updating using POST (to add),
    //          DELETE (to remove) along with PATCH (to replace).
    //          Need to coalesce the POST, PATCH, and DELETE handlers!

    // Mount the resource graph.
    //$app->group('', Graph\Adapters\Slim\Adapter::class);

    return $app;
})();


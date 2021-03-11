<?php

declare(strict_types=1);

use ThePetPark\Http\Auth;

/**
 * Map API endpoints to authentication controllers.
 *
 * The provided controllers must be defined in the app's dependency container.
 */
return function (Slim\App $auth) {
    
    $auth->map(['POST'], '/login', Auth\Login::class);
    $auth->map(['GET'], '/logout', Auth\Logout::class);
    $auth->map(['POST'], '/register', Auth\Register::class);
    $auth->map(['GET'], '/echo', Auth\EchoSession::class);

};
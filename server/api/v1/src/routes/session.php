<?php

declare(strict_types=1);

use ThePetPark\Http\Auth;

/**
 * Map the session endpoint to authentication controllers.
 *
 * The provided controllers must be defined in the app's dependency container.
 */
return function (Slim\App $session) {
    $session->map(['GET'],    '', Auth\WhoAmI::class);
    $session->map(['POST'],   '', Auth\Login::class);
    $session->map(['DELETE'], '', Auth\Logout::class);
};

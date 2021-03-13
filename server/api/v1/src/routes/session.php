<?php

declare(strict_types=1);

use ThePetPark\Http\Session;

/**
 * Map the session endpoint to authentication controllers.
 *
 * The provided controllers must be defined in the app's dependency container.
 */
return function (Slim\App $session) {
    $session->map(['GET'],    '', Session\Show::class);
    $session->map(['POST'],   '', Session\Create::class);
    $session->map(['DELETE'], '', Session\Destroy::class);
};

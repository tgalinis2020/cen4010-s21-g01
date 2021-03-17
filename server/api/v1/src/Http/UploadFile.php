<?php

declare(strict_types=1);

namespace ThePetPark\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Middleware\Session;

/**
 * Raw images must be uploaded before they can be used with posts and avatars.
 * Returns the path to the image on success.
 */
final class UploadFile
{
    public function __invoke(Request $req, Response $res): Response
    {
        $user = $req->getAttribute(Session::TOKEN);

        // Unauthenticated users cannot upload images.
        if ($user === null) {
            return $res->withStatus(401);
        }

        // TODO: move uploaded file to /img directory.
        // Rename file using current date and time.

        return $res->withStatus(201);
    }
}


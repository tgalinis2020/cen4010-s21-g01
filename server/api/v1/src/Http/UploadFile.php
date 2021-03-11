<?php

declare(strict_types=1);

namespace ThePetPark\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Raw images must be uploaded before they can be used with posts and avatars.
 */
final class UploadFile
{
    public function __construct()
    {
    }

    public function __invoke(Request $req, Response $res): Response
    {
        return $res->withStatus(201);
    }
}


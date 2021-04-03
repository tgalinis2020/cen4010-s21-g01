<?php

declare(strict_types=1);

namespace ThePetPark\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Middleware\Auth\Session;

use function sprintf;
use function json_encode;
use function bin2hex;
use function random_bytes;
use function time;

/**
 * Raw images must be uploaded before they can be used with posts and avatars.
 * Returns the path to the image on success.
 */
final class UploadFile
{
    /** @var string */
    private $uploads;

    public function __construct(array $uploadDirectory)
    {
        $this->uploads = $uploadDirectory;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        $user = $req->getAttribute(Session::TOKEN);

        // Unauthenticated users cannot upload images.
        if ($user === null) {
            return $res->withStatus(401);
        }

        /** @var \Psr\Http\Message\UploadedFileInterface[] */
        $uploadedFiles = $req->getUploadedFiles();

        $uploadedFile = $uploadedFiles['data'];

        switch ($uploadedFile->getError()) {
        case UPLOAD_ERR_OK:
            $ext = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $file = sprintf('%x_%x.%0.8s', bin2hex(random_bytes(4)), time(), $ext);

            $uploadedFile->moveTo(
                $this->uploads['root']
                . $this->uploads['endpoint']
                . '/'
                . $file
            );

    
            $res->getBody()->write(json_encode([
                'data' => $this->uploads['base'] . $this->uploads['endpoint'] . '/' . $file,
            ]));
    
            return $res->withStatus(201);
        
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
        case UPLOAD_ERR_NO_FILE:
            return $res->withStatus(400);
            
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
        case UPLOAD_ERR_PARTIAL:
        case UPLOAD_ERR_EXTENSION:
        default:
            return $res->withStatus(500);
        }
    }
}


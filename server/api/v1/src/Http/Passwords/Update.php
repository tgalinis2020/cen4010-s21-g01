<?php

declare(strict_types=1);

namespace ThePetPark\Http\Passwords;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Exception;

use function json_decode;
use function password_hash;
use function password_verify;

final class Update
{
    /** @var \Doctrine\DBAL\Connection */
    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $document = json_decode((string) $request->getBody(), true);
        $id = $request->getAttribute('id');
        $passwords = $document['data'] ?? [];

        if (empty($passwords)) {
            return $response->withStatus(400);
        }

        $oldPassword = $passwords['current'] ?? null;
        $newPassword = $passwords['new'] ?? null;

        if ($oldPassword === null || $newPassword === null) {
            return $response->withStatus(400);
        }

        $qb = $this->conn->createQueryBuilder();

        $fromDB = $qb->select('passwd')
            ->from('user_passwords')
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
            ->execute()
            ->fetchColumn(0);

        if ($fromDB === null) {
            return $response->withStatus(404); // User has no password on file.
        }

        if (password_verify($oldPassword, $fromDB) === false) {
            return $response->withStatus(401); // Passwords did not match.
        }

        $newPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        try {

            $qb = $this->conn->createQueryBuilder();
            
            $qb->update('user_passwords')
                ->set('passwd', $qb->createNamedParameter($newPassword))
                ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
                ->execute();
            
        } catch (Exception $e) {

            // An exception will occur if attempting to insert a password for
            // a user that already has a password.

            return $response->withStatus(409);

        }

        return $response->withStatus(204);
    }
}
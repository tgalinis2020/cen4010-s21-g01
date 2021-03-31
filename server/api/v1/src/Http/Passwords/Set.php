<?php

declare(strict_types=1);

namespace ThePetPark\Http\Passwords;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Exception;

use function json_decode;
use function password_hash;

final class Set
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
        $password = $document['data'] ?? null;

        if ($password === null) {
            return $response->withStatus(400);
        }

        $password = password_hash($password, PASSWORD_BCRYPT);

        try {

            $qb = $this->conn->createQueryBuilder();
            
            $qb->insert('user_passwords')
                ->setValue('id', $qb->createNamedParameter($id))
                ->setValue('passwd', $qb->createNamedParameter($password))
                ->execute();
            
        } catch (Exception $e) {

            // An exception will occur if attempting to insert a password for
            // a user that already has a password.

            return $response->withStatus(409);

        }

        return $response->withStatus(201);
    }
}
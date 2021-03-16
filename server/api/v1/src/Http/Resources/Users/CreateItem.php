<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Users;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use ThePetPark\Idp;
use Exception;

use function json_decode;
use function password_hash;
use function count;
use function date;

/**
 * Creates a new user account if it doesn't already exist.
 * 
 * Returns:
 *  - 201 on account creation
 *  - 400 on malformed request body
 *  - 409 if provided email is already registered
 */
final class CreateItem
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        $data = json_decode($req->getBody(), true);
        $acct = $data['data'] ?? [];
        $required = ['email', 'username', 'firstName', 'lastName', 'password'];
        $keys = array_keys($acct);
        $diff = array_diff($keys, $required);

        // Can't continue if there isn't enough data to create an account.
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        try {

            $qb = $this->conn->createQueryBuilder();

            $qb->insert('users')
                ->values([
                    'email'      => $qb->createNamedParameter($acct['email']),
                    'username'   => $qb->createNamedParameter($acct['username']),
                    'first_name' => $qb->createNamedParameter($acct['firstName']),
                    'last_name'  => $qb->createNamedParameter($acct['lastName']),
                    'created_at' => date('c'),
                    'idp_code'   => Idp::THEPETPARK,
                ])
                ->execute();

            $acct['id'] = $this->conn->lastInsertId();
            $password = password_hash($acct['password'], PASSWORD_BCRYPT);
    
            $qb = $this->conn->createQueryBuilder();
            
            $qb->insert('user_passwords')
                ->values([
                    'id' => $qb->createNamedParameter($acct['id'], ParameterType::INTEGER),
                    'passwd' => $qb->createNamedParameter($password),
                ])
                ->execute();
            
        } catch (Exception $e) {

            // E-mail and username have a unique key constraint.
            // Query will fail if provided e-mail and/or username are
            // already associated with an account. Client apps should
            // GET /users to check if an account exists before trying
            // to create a new one.

            return $res->withStatus(409);

        }

     

        return $res->withStatus(201);
    }
}

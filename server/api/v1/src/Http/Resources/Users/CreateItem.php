<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Users;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
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

        /*
        $count = (int) $this->conn->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('users')
            ->where('email = ?')
            ->orWhere('username = ?')
            ->setParameter(0, $data['email'])
            ->setParameter(1, $data['username'])
            ->execute()
            ->fetchColumn(0);

        // If an account was found with provided credentials, stop here.
        if ($count > 0) {
            return $res->withStatus(409);
        }
        */

        try {

            $this->conn->createQueryBuilder()
                ->insert('users')
                ->setValue('email', '?')
                ->setValue('username', '?')
                ->setValue('first_name', '?')
                ->setValue('last_name', '?')
                ->setValue('created_at', '?')
                ->setValue('idp_code', '?')
                ->setParameter(0, $acct['email'])
                ->setParameter(1, $acct['username'])
                ->setParameter(2, $acct['firstName'])
                ->setParameter(3, $acct['lastName'])
                ->setParameter(4, date('c'))
                ->setParameter(5, Idp::THEPETPARK)
                ->execute();
            
        } catch (Exception $e) {

            // E-mail and username have a unique key constraint.
            // Query will fail if provided e-mail and/or username are
            // already associated with an account. Client apps should
            // GET /users to check if an account exists before trying
            // to create a new one.

            return $res->withStatus(409);

        }

        $acct['id'] = $this->conn->lastInsertId();
        $password = password_hash($acct['password'], PASSWORD_BCRYPT);

        $this->conn->createQueryBuilder()
            ->insert('user_passwords')
            ->setValue('id', '?')
            ->setValue('passwd', '?')
            ->setParameter(0, $acct['id'])
            ->setParameter(1, $password)
            ->execute();

        return $res->withStatus(201);
    }
}

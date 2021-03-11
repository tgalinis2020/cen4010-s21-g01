<?php

declare(strict_types=1);

namespace ThePetPark\Http\Auth;

use Slim\Http\Request;
use Slim\Http\Response;
use Doctrine\DBAL\Connection;

use function json_decode;
use function date;
use function count;

/**
 * Creates a new user account if it doesn't already exist.
 * 
 * Returns:
 *  - 201 on account creation
 *  - 400 on malformed request body
 *  - 403 if provided email is already registered
 */
final class Register
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    /**
     * @param \Doctrine\DBAL\Connection $conn The connection to the database
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        $data = json_decode($req->getBody(), true);

        $required = ['email', 'username', 'firstName', 'lastName', 'password',
                     'avatar'];
        $keys = array_keys($data['data'] ?? []);
        $diff = array_diff($keys, $required);

        // Can't continue if there isn't enough data to create an account.
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        $count = (int) $this->conn->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('users')
            ->where('email = ?')
            ->setParameter(0, $data['email'])
            ->execute()
            ->fetch();

        if ($count > 0) {
            return $res->withStatus(403);
        }
        
        $this->conn->createQueryBuilder()
            ->insert('users')
            ->setValue('username', '?')
            ->setValue('first_name', '?')
            ->setValue('last_name', '?')
            ->setValue('email', '?')
            ->setValue('created_at', '?')
            ->setParameter(0, $data['username'])
            ->setParameter(1, $data['firstName'])
            ->setParameter(2, $data['lastName'])
            ->setParameter(3, $data['email'])
            ->setParameter(4, date("c"))
            ->execute();

        $id = $this->conn->lastInsertId();

        $this->conn->createQueryBuilder()
            ->insert('user_passwords')
            ->setValue('id', '?')
            ->setValue('passwd', '?')
            ->setParameter(0, $id)
            ->setParameter(1, password_hash($data['password'], PASSWORD_BCRYPT))
            ->execute();

        return $res->withStatus(201, 'Created');
    }
}


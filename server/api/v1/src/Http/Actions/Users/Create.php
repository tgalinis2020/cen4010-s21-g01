<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Users;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Library\Graph\ActionInterface;
use ThePetPark\Library\Graph\Graph;
use ThePetPark\Idp;

use Exception;

use function json_decode;
use function array_diff;
use function array_keys;
use function date;

class Create implements ActionInterface
{
    public function execute(Graph $graph, Request $req, Response $res): Response
    {
        $body = json_decode($req->getBody(), true);
        $conn = $graph->getConnection();
        $acct = $body['data'];
        $required = ['email', 'username', 'firstName', 'lastName', 'password'];
        $keys = array_keys($acct);
        $diff = array_diff($keys, $required);

        // Can't continue if there isn't enough data to create an account.
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        try {

            $qb = $conn->createQueryBuilder();

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
                    'id'     => $qb->createNamedParameter($acct['id']),
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
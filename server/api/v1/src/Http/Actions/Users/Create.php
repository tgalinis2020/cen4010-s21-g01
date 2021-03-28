<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Users;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph;
use ThePetPark\Idp;

use Exception;

use function json_decode;
use function array_diff;
use function array_keys;
use function date;
use function password_hash;
use function htmlentities;

class Create implements Graph\ActionInterface
{
    /** @var \Doctrine\DBAL\Connection */
    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function execute(Graph\App $graph, Request $req): Response
    {
        $res = $graph->createResponse();
        $document = json_decode($req->getBody(), true);
        
        if (isset($document['data'], $document['data']['attributes']) === false) {
            return $res->withStatus(400);
        }
        
        $data = $document['data'];
        $acct = $data['attributes'];
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
                    'username'   => $qb->createNamedParameter(htmlentities($acct['username'], ENT_QUOTES)),
                    'first_name' => $qb->createNamedParameter(htmlentities($acct['firstName'], ENT_QUOTES)),
                    'last_name'  => $qb->createNamedParameter(htmlentities($acct['lastName'], ENT_QUOTES)),
                    'created_at' => $qb->createNamedParameter(date('c')),
                    'idp_code'   => $qb->createNamedParameter(Idp::THEPETPARK),
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
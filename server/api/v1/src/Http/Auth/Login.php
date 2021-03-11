<?php

declare(strict_types=1);

namespace ThePetPark\Http\Auth;

use Slim\Http\Request;
use Slim\Http\Response;
use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;

use function time;
use function setcookie;

/**
 * If the provided username exists and provided password's hash matches
 * the one in the database, set a httponly cookie with the user's session JWT.
 * 
 * TODO: might be a good idea to handle making the JWT in a separate component
 * considering the same cookie name is being used in multiple parts of the
 * application.
 */
final class Login
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    /** @var string */
    private $key;

    /** @var string */
    private $alg;

    /**
     * @param string $key The secret key used for generating JWTs
     * @param \Doctrine\DBAL\Connection $conn The connection to the database
     */
    public function __construct(string $alg, string $key, Connection $conn)
    {
        $this->alg = $alg;
        $this->key = $key;
        $this->conn = $conn;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        $data = $req->getAttribute('data');

        $account = $this->conn->createQueryBuilder()
            ->select('a.id', 'a.username', 'a.email', 'b.passwd',
                     'a.first_name', 'a.last_name', 'a.avatar_url')
            ->from('users', 'a')
            ->join('a', 'user_passwords', 'b', 'a.id = b.id')
            ->where('a.email = ?')
            ->setParameter(0, $data['email'])
            ->execute()
            ->fetch();

        // If the user account was not found or the passwords did not match,
        // return a 404 Not Found status code to the client.
        if (!password_verify($data['password'], $account['passwd'])) {
            return $res->withStatus(404, 'Not Found');
        }

        $currentTime = time();
        $root = '/~cen4010_s21_g01';
        $host = $req->getUri()->getHost();
        $domain = $host . $root;
        $expiry = $currentTime + 60*60*24*7; // 7 days (in seconds)

        $payload = [
            'iat'           => $currentTime,
            'exp'           => $expiry,
            'iss'           => $domain,
            'sub'           => $domain,
            'id'            => $account['id'],
            'username'      => $account['username'],
            'first_name'    => $account['first_name'],
            'last_name'     => $account['last_name'],
            'avatar_url'    => $account['avatar_url'],
        ];

        $token = JWT::encode($payload, $this->key, $this->alg);

        setcookie('session_token', $token, $expiry, $root, $host, true, true);

        // Cookie was set, let the client know using the 201 Created status code.
        return $res->withStatus(201, 'Created');
    }
}


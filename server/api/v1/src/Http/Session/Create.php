<?php

declare(strict_types=1);

namespace ThePetPark\Http\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Services\JWT\Encoder;
use ThePetPark\Middleware\Session;

use function time;
use function setcookie;

/**
 * If the provided username exists and provided password's hash matches
 * the one in the database, set a httponly cookie with the user's session JWT.
 */
final class Create
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    /** @var \ThePetPark\Services\JWT\Encoder */
    private $encoder;

    public function __construct(Connection $conn, Encoder $jwtEncoder)
    {
        $this->conn = $conn;
        $this->encoder = $jwtEncoder;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        $data = json_decode($req->getBody(), true);
        $qb = $this->conn->createQueryBuilder();
        $username = $qb->createNamedParameter($data['username']);
        $acct = $qb
            ->select([
                'u.id',
                'u.idp_code    idpCode',
                'u.first_name  firstName',
                'u.last_name   lastName',
                'u.avatar_url  avatar',
                'u.created_at  createdAt',
                'p.passwd      password',
            ])
            ->from('users', 'u')
            ->join('u', 'user_passwords', 'p', 'p.id = u.id')
            ->where('u.username = ' . $username)
            ->orWhere('u.email = ' . $username)
            ->execute()
            ->fetch();

        // If the user account was not found or the passwords did not match,
        // return a 404 Not Found status code to the client.
        if (password_verify($data['password'], $acct['password']) === false) {
            return $res->withStatus(404);
        }

        $currentTime = time();
        $root = '/~cen4010_s21_g01';
        $host = $req->getUri()->getHost();
        $domain = $host . $root;
        $expiry = $currentTime + 60*60*24*7; // 7 days (in seconds)

        $payload = [
            'iat' => $currentTime,
            'exp' => $expiry,
            'iss' => $domain,
            'aud' => $domain,
        ];

        // JWTs are not encrypted! Don't leak the password.
        unset($acct['password']);

        $payload += $acct;

        $token = $this->encoder->encode($payload);

        setcookie(Session::TOKEN, $token, $expiry, $root, $host, true, true);

        // Cookie was set, let the client know using the 201 status code.
        return $res->withStatus(201);
    }
}


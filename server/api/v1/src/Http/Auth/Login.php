<?php

declare(strict_types=1);

namespace ThePetPark\Http\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Repositories\UserRepository;

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
    /** @var \ThePetPark\Repositories\UserRepository */
    private $userRepo;

    /** @var callable */
    private $encoder;

    public function __construct(UserRepository $userRepo, callable $jwtEncoder)
    {
        $this->userRepo = $userRepo;
        $this->encoder = $jwtEncoder;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        $data = json_decode($req->getBody(), true);

        $account = $this->userRepo->getUsers()
            ->where('username = :username')
            ->orWhere('email = :username')
            ->setParameter(0, $data['username'])
            ->execute()
            ->fetch();

        // If the user account was not found or the passwords did not match,
        // return a 404 Not Found status code to the client.
        if (password_verify($data['password'], $account['password']) === false) {
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
            'sub' => $domain,
        ];

        // JWTs are not encrypted! Don't leak the password.
        unset($account['password']);

        $payload += $account;

        $token = ($this->encoder)($payload);

        setcookie('session_token', $token, $expiry, $root, $host, true, true);

        // Cookie was set, let the client know using the 201 status code.
        return $res->withStatus(201);
    }
}


<?php

declare(strict_types=1);

namespace ThePetPark\Http\Auth;

use Slim\Http\Request;
use Slim\Http\Response;
use Doctrine\DBAL\Connection;
use Exception;
use ThePetPark\Repositories\UserRepository;

use function json_decode;
use function date;
use function count;

/**
 * Creates a new user account if it doesn't already exist.
 * 
 * Returns:
 *  - 201 on account creation
 *  - 400 on malformed request body
 *  - 409 if provided email is already registered
 */
final class Register
{
    /** @var \ThePetPark\Repositories\UserRepository */
    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        $data = json_decode($req->getBody(), true);

        $required = ['email', 'username', 'firstName', 'lastName', 'password'];
        $keys = array_keys($data['data'] ?? []);
        $diff = array_diff($keys, $required);

        // Can't continue if there isn't enough data to create an account.
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        try {

            // This throws an exception if the user already exists in the DB.
            $this->userRepo->createUser(
                $data['email'], $data['password'], $data['username'],
                $data['firstName'], $data['lastName']
            );

        } catch (Exception $e) {

            return $res->withStatus(409);

        }

        return $res->withStatus(201);
    }
}

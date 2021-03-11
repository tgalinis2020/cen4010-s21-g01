<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Users;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Repositories\UserRepository;
use Exception;

use function json_decode;
use function password_hash;
use function count;

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

        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        try {

            // This throws an exception if the user already exists in the DB.
            $this->userRepo->createUser(
                $data['email'], $password, $data['username'],
                $data['firstName'], $data['lastName']
            );

        } catch (Exception $e) {

            return $res->withStatus(409);

        }

        return $res->withStatus(201);
    }
}

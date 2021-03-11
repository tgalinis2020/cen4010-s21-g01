<?php

declare(strict_types=1);

namespace ThePetPark\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Repositories\UserRepository;
use ThePetPark\Repositories\PetRepository;
use ThePetPark\Repositories\PostRepository;

use function parse_str;
use function json_encode;

/**
 * This endpoint yields users (humans), pets and posts that match the
 * search critera.
 * 
 * The search critera must be provided in the request's query string
 * using the "q" key.
 * 
 * Response Codes:
 *   - 200 on successful lookup
 *   - 400 if "q" query parameter is missing
 *   - 404 if no results found
 */
final class Search
{
    /** @var \ThePetPark\Repositories\UserRepository */
    private $userRepo;

    /** @var \ThePetPark\Repositories\PetRepository */
    private $petRepo;

    /** @var \ThePetPark\Repositories\PostRepository */
    private $postRepo;

    public function __construct(
        UserRepository $userRepo,
        PetRepository $petRepo,
        PostRepository $postRepo
    ) {
        $this->userRepo = $userRepo;
        $this->petRepo = $petRepo;
        $this->postRepo = $postRepo;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        parse_str($req->getUri()->getQuery(), $params);
    
        // Can't do anything without a search term. Stop here if it is missing.
        if (!isset($params['q'])) {
            return $res->withStatus(400);
        }

        // TODO: parse the q parameter for specific tokens, like #tags or @users
        // Maybe assume tags by default?
        $term = $params['q'];

        $criteria = [
            'tags'          => [],
            'pets'          => [],
            'pet_breeds'    => [],
            'pet_types'     => [],
            'users'         => [],
        ];

        // Get users that match criteria
        $builder = $this->userRepo->getUsers();
        
        // TODO: apply applicable search critera

        $users = $builder->execute()->fetchAll();


        // Get pets that match criteria
        $builder = $this->petRepo->getPets();

        // TODO: apply applicable search criteria

        $pets = $builder->execute()->fetchAll();


        // Get posts that match criteria
        $$builder = $this->postRepo->getPosts();

        // TODO: apply applicable search criteria

        $posts = $builder->execute()->fetchAll();

        $results = [
            'users' => $users,
            'pets'  => $pets,
            'posts' => $posts,
        ];

        $body = $res->getBody();

        $body->write(json_encode(['data' => $results]));

        $count = count($users) + count($pets) + count($posts);

        return $res->withStatus($count > 0 ? 200 : 404);
    }
}


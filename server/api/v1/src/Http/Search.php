<?php

declare(strict_types=1);

namespace ThePetPark\Http;

use Slim\Http\Request;
use Slim\Http\Response;
use Doctrine\DBAL\Connection;

use function parse_str;

/**
 * This endpoint yields users (humans), pets and posts that match the
 * search critera.
 * 
 * The search critera must be provided in the request's query string
 * using the "q" key.
 */
final class Search
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
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
        $users = $this->conn->createQueryBuilder()
            ->select('id', 'email', 'first_name as firstName',
                     'last_name as lastName', 'username')
            ->from('users')
            //->where(/* Magic */)
            ->execute()
            ->fetchAll();


        // Get pets that match criteria
        $pets = $this->conn->createQueryBuilder()
            ->select('a.id', 'a.pet_name as name', 'b.pet_type as type',
                     'c.pet_breed as breed')
            ->from('pets', 'a')
            ->join('a', 'pet_types',  'b', 'a.pet_type_id = b.id')
            ->join('a', 'pet_breeds', 'c', 'a.pet_breed_id = c.id')
            //->where(/* Magic */)
            ->execute()
            ->fetchAll();


        // Get posts that match criteria
        $posts = $this->conn->createQueryBuilder()
            ->select('a.id', 'a.image_url', 'a.text_conent', 'b.likes')
            ->from('posts', 'a')
            ->join('a', 'likeables', 'b', 'p.likeable_id = l.id')
            //->where(/* Magic */)
            ->execute()
            ->fetchAll();

        $results = [
            'users' => $users,
            'pets'  => $pets,
            'posts' => $posts,
        ];

        return $res->withJson(['data' => $results], 200);
    }
}


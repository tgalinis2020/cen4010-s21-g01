<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Posts;

use Slim\Http\Request;
use Slim\Http\Response;
use Doctrine\DBAL\Connection;

use function json_encode;

final class CreateItem
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function handle(Request $req, Response $res): Response
    {
        $user = $req->getAttribute('session');

        // Only authenticated users can create posts.
        if ($user === null) {
            return $res->withStatus(401);
        }

        $data = json_decode($req->getBody(), true);

        $required = ['text_content', 'image_url', 'tags', 'pets'];
        $keys = array_keys($data['data'] ?? []);
        $diff = array_diff($keys, $required);

        // Can't continue if there isn't enough data to create an account.
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        $data = $data['data'];

        // Posts are likeable: create the likable entry first.
        $this->conn->createQueryBuilder()
            ->insert('likeables')
            ->setValue('like_count', '?')
            ->setParameter(0, 0)
            ->execute();

        $likeableID = $this->conn->lastInsertId();

        $this->conn->createQueryBuilder()
            ->insert('posts')
            ->setValue('text_content', '?')
            ->setValue('image_url', '?')
            ->setValue('likeable_id', '?')
            ->setParameter(0, $data['text_content'])
            ->setParameter(1, $data['image_url'])
            ->setParameter(2, $likeableID)
            ->execute();

        $postID = $this->conn->lastInsertId();

        // Return the newly created post's ID.
        // Will be required by the client app to make associations with
        // tags and pets.
        $res->getBody()->write(json_encode(['data' => ['id' => $postID]]));
        
        return $res->withStatus(201);
    }
}


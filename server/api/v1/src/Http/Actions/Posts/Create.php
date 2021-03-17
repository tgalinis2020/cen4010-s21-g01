<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Posts;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Middleware\Session;
use ThePetPark\Library\Graph\ActionInterface;
use ThePetPark\Library\Graph\Graph;

use function json_decode;
use function array_diff;
use function array_keys;

class Create implements ActionInterface
{
    public function execute(Graph $graph, Request $req, Response $res): Response
    {
        $conn = $graph->getConnection();
        $user = $req->getAttribute(Session::TOKEN);

        // Only authenticated users can create posts.
        if ($user === null) {
            return $res->withStatus(401);
        }

        $data = json_decode($req->getBody(), true);

        $required = ['text', 'image', 'tags', 'pets'];
        $keys = array_keys($data['data'] ?? []);
        $diff = array_diff($keys, $required);

        // Can't continue if there isn't enough data to create an account.
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        $data = $data['data'];

        // Posts are likeable: create the likable entry first.
        $conn->createQueryBuilder()
            ->insert('likeables')
            ->setValue('like_count', '?')
            ->setParameter(0, 0)
            ->execute();

        $likeableID = $this->conn->lastInsertId();

        $conn->createQueryBuilder()
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
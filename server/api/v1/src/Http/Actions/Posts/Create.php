<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Posts;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Middleware\Session;
use ThePetPark\Library\Graph;

use function json_decode;
use function array_diff;
use function array_keys;
use function count;
use function date;
use function htmlentities;
use function strtolower;

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
        $session = $req->getAttribute(Session::TOKEN);

        // Only authenticated users can create posts.
        if ($session === null) {
            return $res->withStatus(401);
        }

        $data = json_decode($req->getBody(), true);

        $required = ['title', 'image', 'tags', 'pets'];
        $keys = array_keys($data['data'] ?? []);
        $diff = array_diff($keys, $required);

        // Can't continue if there isn't enough data to create an account.
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        $data = $data['data'];
        $attributes = $data['attributes'];
        $attributes['title'] = htmlentities($attributes['title'], ENT_QUOTES);
        $attributes['image'] = htmlentities($attributes['image'], ENT_QUOTES);
        $createdAt = date('c');

        $qb = $this->conn->createQueryBuilder();

        $qb->insert('posts')
            ->setValue('title', $qb->createNamedParameter($attributes['title']))
            ->setValue('image_url', $qb->createNamedParameter($attributes['image']))
            ->setValue('user_id', $qb->createNamedParameter($session['id']))
            ->setValue('created_at', $qb->createNamedParameter($createdAt));

        if (isset($attributes['text'])) {
            $attributes['text'] = htmlentities($attributes['text'], ENT_QUOTES);

            $qb->setValue('text_content', $qb->createNamedParameter($attributes['text']));
        }

        $qb->execute();

        $postID = $this->conn->lastInsertId();

        // Return the newly created post's ID.
        // Will be required by the client app to make associations with
        // tags and pets.
        $document = [
            'jsonapi' => '1.0',
            'data' => [
                'type' => 'posts',
                'id' => $postID,
                'attributes' => [
                    'title' => $attributes['title'],
                    'image' => $attributes['image'],
                    'text' => $attributes['text'] ?? null,
                    'createdAt' => $createdAt,
                ],
                'relationships' => [
                    'author' => [
                        'type' => 'users',
                        'id' => $session['id'],
                    ]
                ]
            ]
        ];

        $res->getBody()->write(json_encode($document));
        
        return $res->withStatus(201);
    }
}
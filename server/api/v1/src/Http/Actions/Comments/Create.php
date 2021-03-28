<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Middleware\Session;
use ThePetPark\Library\Graph;

use function json_decode;
use function array_diff;
use function array_keys;

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

        if ($session === null) {
            return $res->withStatus(401);
        }

        $document = json_decode($req->getBody(), true);

        if (isset($document['data'], $document['data']['attributes'],
                $document['data']['relationships']) === false
        ) {
            return $res->withStatus(400);
        }

        $data = $document['data'];
        $attributes = $data['attributes'];
        $relationships = $data['relationships'];

        // This is some galaxy-brain validation.
        //
        // TODO:    Refactor if time allows for it!
        if (isset($attributes['text'], $relationships['post'],
                $relationships['post']['data'],
                $relationships['post']['data']['type'],
                $relationships['post']['data']['id']) === false
            || $relationships['post']['data']['type'] !== 'posts'
        ) {
            return $res->withStatus(400);
        }

        $postID = $relationships['post']['data']['id'];

        $qb = $this->conn->createQueryBuilder();

        $attributes['createdAt'] = date('c');

        $qb->insert('comments')
            ->values([
                'text_content' => $qb->createNamedParameter($attributes['text']),
                'user_id'      => $qb->createNamedParameter($session['id']), 
                'created_at'   => $qb->createNamedParameter($attributes['createdAt']),
            ])
            ->execute();
        
        $commentID = $this->conn->lastInsertId();

        $qb = $this->conn->createQueryBuilder();

        $qb->insert('post_comments')
            ->values([
                'post_id'    => $qb->createNamedParameter($postID),
                'comment_id' => $qb->createNamedParameter($commentID),
            ])
            ->execute();

        $document = [
            'jsonapi' => '1.0',
            'data' => [
                'id' => $commentID,
                'attributes' => [
                    'text' => $attributes['text'],
                    'createdAt' => $attributes['createdAt'],
                ]
            ],
        ];

        $res->getBody()->write(json_encode($document));

        return $res->withStatus(201);
    }
}
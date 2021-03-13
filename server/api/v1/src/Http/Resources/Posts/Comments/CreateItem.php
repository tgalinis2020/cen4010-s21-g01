<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Posts\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;

use function json_encode;
use function json_decode;

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
        $session = $req->getAttribute('session');

        if ($session === null) {
            return $res->withStatus(401);
        }

        $data = json_decode($req->getBody(), true);

        $comment = $data['data'];

        $this->conn->createQueryBuilder()
            ->insert('post_comments')
            ->setValue('text_content', '?')
            ->setValue('user_id', '?')
            ->setParameter(0, $comment['textContent'])
            ->setParameter(1, $session['id'])
            ->execute();
        
        $commentID = $this->conn->lastInsertId();

        // Although it is unlikely a client would need the newly created comment
        // ID, it is returned for the sake of consistency.
        $res->getBody()->write(json_encode(['data' => ['id' => $commentID]]));

        return $res->withStatus(201);
    }
}


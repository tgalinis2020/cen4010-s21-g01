<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Posts\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;

final class UpdateItem
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

        $body = json_decode($req->getBody(), true);

        if (!isset($body['data'])) {
            return $res->withStatus(400);
        }

        $nfields = 0;
        $data = $body['data'];
        $q = $this->conn->createQueryBuilder()->update('post_comments');

        if (isset($data['textContent'])) {

            $nfields++;

            $q->set('text_content', ':text')
                ->setParameter(':text', $data['textContent']);

        }

        // If no fields were given, stop here.
        if ($nfields === 0) {
            return $res->withStatus(200);
        }

        $q->execute();

        return $res->withStatus(201);
    }
}


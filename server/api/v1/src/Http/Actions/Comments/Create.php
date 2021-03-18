<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Middleware\Session;
use ThePetPark\Library\Graph;

use function json_decode;
use function array_diff;
use function array_keys;

class Create implements Graph\ActionInterface
{
    public function execute(Graph\App $graph, Request $req): Response
    {
        $res = $graph->createResponse();
        $conn = $graph->getConnection();
        $session = $req->getAttribute(Session::TOKEN);

        if ($session === null) {
            return $res->withStatus(401);
        }

        $data = json_decode($req->getBody(), true);

        $comment = $data['data'];

        $conn->createQueryBuilder()
            ->insert('post_comments')
            ->setValue('text_content', '?')
            ->setValue('user_id', '?')
            ->setParameter(0, $comment['text'])
            ->setParameter(1, $session['id'])
            ->execute();
        
        $commentID = $conn->lastInsertId();

        // Although it is unlikely a client would need the newly created comment
        // ID, it is returned for the sake of consistency.
        $res->getBody()->write(json_encode(['data' => ['id' => $commentID]]));

        return $res->withStatus(201);
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Middleware\Session;
use ThePetPark\Library\Graph;

use function json_decode;

class Update implements Graph\ActionInterface
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

        if (isset($document['data'], $document['data']['id'],
                $document['data']['attributes']) === false
        ) {
            return $res->withStatus(400);
        }

        $data = $document['data'];
        $commentID = $data['id'];
        $attributes = $data['attributes'];

        $qb = $this->conn->createQueryBuilder();

        $qb->update('comments')->where($qb->expr()->eq('id', $commentID));

        if (isset($attributes['text'])) {
            $qb->set('text_content', $qb->createNamedParameter($attributes['text']));
        }

        $qb->execute();

        return $res->withStatus(204);
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Posts;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph;
use ThePetPark\Middleware\Session;

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
        $document = json_decode($req->getBody(), true);

        $res = $graph->createResponse();
        $session = $req->getAttribute(Session::TOKEN);

        if ($session === null) {
            return $res->withStatus(401);
        }

        $document = json_decode($req->getBody(), true);
        $postID = $req->getAttribute(Graph\App::PARAM_ID);

        if (isset($document['data'],
                $document['data']['attributes'], $document['data']['type'],
                $document['data']['id'], $document['data']['relationships']) === false
            || $postID === null
            || $document['data']['type'] !== 'posts'
            || $document['data']['id'] !== $postID
        ) {
            return $res->withStatus(400);
        }

        $data = $document['data'];
        $attributes = $data['attributes'];
        $relationships = $data['relationships'];

        $qb = $this->conn->createQueryBuilder();
        $qb->update('posts')->where($qb->expr()->eq('id', $postID));

        $attrs = [
            ['title', 'title'],
            ['text', 'text_content'],
            ['image', 'image_url'],
        ];

        foreach ($attrs as list($attr, $impl)) {
            if (isset($attributes[$attr])) {
                $attributes[$attr] = htmlentities($attributes[$attr], ENT_QUOTES);

                $qb->set($impl, $qb->createNamedParameter($attributes[$attr]));
            }
        }

        if (isset($attributes['tags'])) {

            // TODO:    Check to see if provided tags exist. If not, create them
            //          and get their IDs. Remove previos tag associations and
            //          use new tag IDs.

        }

        if (isset($relationships['pets'])) {
            
            // TODO:    Remove previous associations and create new ones using
            //          provided IDs.

        }

        return $res->withStatus(501);
    }
}
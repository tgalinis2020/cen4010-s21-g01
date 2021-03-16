<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
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
        $body = json_decode($req->getBody(), true);
        $post = $body['data'];
        $required = ['textContent', 'image'];
        $keys = array_keys($post);
        $diff = array_diff($keys, $required);

        // TODO: look at author relationship to get author ID

        // Can't continue if there isn't enough data to create an account.
        /*
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }
        */

        return $res->withStatus(501);
    }
}
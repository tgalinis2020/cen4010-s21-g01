<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Posts\Comments;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;

use function json_encode;

/**
 * Returns a collection of post comments along with any associated information,
 * such as their authors.
 */
final class Fetch
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function __invoke(Request $req, Response $res): Response
    {
        parse_str($req->getUri()->getQuery(), $params);

        $postID = $req->getAttribute('post_id');

        $q = $this->conn->createQueryBuilder();

        $filters = $q->expr()->andX();

        $filters->add($q->expr()->eq('post_id', ':post_id'));

        $q->select([
                'pc.id',
                'pc.text_content',
                'pc.user_id',
                'u.username',
                'u.email',
                'u.first_name',
                'u.last_name',
                'u.avatar_url',
            ])
            ->from('post_comments', 'pc')
            ->join('pc', 'users', 'u', 'u.id = pc.user_id')
            ->where($filters)
            ->setParameter(':post_id', $postID);
        
        $comments = [];

        foreach ($q->execute() as $c) {
            $comments[] = [
                'id'            => $c['id'],
                'textContent'   => $c['text_content'],
                'author' => [
                    'id'        => $c['user_id'],
                    'username'  => $c['username'],
                    'email'     => $c['email'],
                    'firstName' => $c['first_name'],
                    'lastName'  => $c['last_name'],
                    'avatar'    => $c['avatar_url'],
                ],
            ];
        }

        $res->getBody()->write(json_encode(['data' => $comments]));

        return $res->withStatus(count($comments) > 0 ? 200 : 404);
    }
}


<?php

declare(strict_types=1);

namespace ThePetPark\Http\Resources\Users;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;

use ThePetPark\Services\Query;

use function json_encode;
use function is_numeric;
use function substr;

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

        $qb = $this->conn->createQueryBuilder();
        $conditions = $qb->expr()->andX();
        $limit = 20;
        $fieldMap = [
            'id'        => 'u.id',
            'email'     => 'u.email',
            'username'  => 'u.username',
            'firstName' => 'u.first_name',
            'lastName'  => 'u.last_name',
            'createdAt' => 'u.created_at',
        ];

        $qb->select([
                'u.id',
                'u.username',
                'u.email',
                'u.first_name AS firstName',
                'u.last_name AS lastName',
                'u.idp_code AS idpCode',
                'u.avatar_url AS avatar',
                'u.created_at AS createdAt',
            ])
            ->from('users', 'u');

        $filters = new Query\Filters($qb, $conditions, $fieldMap);

        // Pagination parameters. Only cursor-based pagination is supported
        // since it's both easy to impelemnt and very efficient.
        if (isset($params['page'])) {
            $page = $params['page'];

            if (isset($page['cursor'])) {
                $conditions->add($this->qb->expr()->gt(
                    'u.id',
                    $this->qb->createNamedParameter($params['after'])
                ));
            }

            if (isset($page['limit']) && is_numeric($params['limit'])) {
                $limit = (int) $params['limit'];
            }

        }

        switch ($filters->apply($params['filter'] ?? [])) {
            case Query\Filters::EINVALIDFIELD:
            case Query\Filters::EINVALIDEXPR:

                // If filters are not properly formatted, return a
                // 400 to the client application.
                return $res->withStatus(400);
        }

        if (isset($params['sort'])) {
            $fields = explode(',', $params['sort']);

            foreach ($fields as $field) {

                switch (substr($field, 0, 1)) {
                case '-':
                    $field = substr($field, 1);
                    $order = 'DESC';
                    break;
                case '+':
                    $field = substr($field, 1);
                }
                
                if (!isset($fieldMap[$field])) {
                    return $res->withStatus(400);
                }
                
                $qb->addOrderBy($fieldMap[$field], $order);

            }
        }

        $users = $qb->where($conditions)
            ->setMaxResults($limit)
            ->execute()
            ->fetchAll();

        $res->getBody()->write(json_encode(['data' => $users]));

        return $res->withStatus(count($users) > 0 ? 200 : 404);
    }
}


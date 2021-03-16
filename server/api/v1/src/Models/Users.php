<?php

declare(strict_types=1);

namespace ThePetPark\Models;

use Doctrine\DBAL\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Library\Graph\Schema;
use ThePetPark\Idp;

final class Users extends Schema
{
    protected function definitions()
    {
        $this->setType('users');

        $this->addAttribute('email');
        $this->addAttribute('username');
        $this->addAttribute('firstName', 'first_name');
        $this->addAttribute('lastName', 'last_name');
        $this->addAttribute('avatar', 'avatar_url');
        $this->addAttribute('idpCode', 'idp_code');
        $this->addAttribute('createdAt', 'created_at');

        $this->hasMany('posts', 'posts', 'user_id');
        $this->hasMany('pets', 'pets', 'user_id');
        $this->hasMany('subscriptions', 'pets', 'user_id', [
            ['subscriptions', 'user_id', 'pet_id'],
        ]);
        $this->hasMany('favorites', 'posts', [
            ['user_favorite_posts', 'user_id', 'post_id']
        ]);
    }

    public function create(Connection $conn, Request $req, Response $res): Response
    {
        $data = json_decode($req->getBody(), true);
        $fields = $data['data'] ?? []; // TODO: assert this is a valid JSON-API document
        $required = ['email', 'username', 'firstName', 'lastName', 'password'];
        $keys = array_keys($fields);
        $diff = array_diff($keys, $required);

        // Can't continue if there isn't enough data to create an account.
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        $qb = $conn->createQueryBuilder();

        $qb->insert('users')
            ->values([
                'email'      => $qb->createNamedParameter($fields['email']),
                'username'   => $qb->createNamedParameter($fields['username']),
                'first_name' => $qb->createNamedParameter($fields['firstName']),
                'last_name'  => $qb->createNamedParameter($fields['lastName']),
                'created_at' => date('c'),
                'idp_code'   => Idp::THEPETPARK,
            ])
            ->execute();

        $qb = $conn->createQueryBuilder();
        
        $qb->insert('user_passwords')
            ->values([
                'id'     => $this->conn->lastInsertId(),
                'passwd' => $qb->createNamedParameter(
                    password_hash($fields['password'], PASSWORD_BCRYPT)
                ),
            ])
            ->execute();

        return $res->withStatus(201);
    }
}
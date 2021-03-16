<?php

declare(strict_types=1);

namespace ThePetPark\Models;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

use ThePetPark\Library\Graph\Schema;
use ThePetPark\Idp;

use Exception;
use function array_diff;
use function array_keys;
use function count;
use function password_hash;

final class Posts extends Schema
{
    protected function definitions()
    {
        $this->setType('posts');

        $this->addAttribute('textContent', 'text_content');
        $this->addAttribute('image', 'image_url');
        $this->addAttribute('createdAt', 'created_at');

        $this->belongsToOne('user', 'users', 'user_id');
        $this->belongsToOne('likes', 'likeables', 'likeable_id');
        $this->hasMany('comments', 'comments', 'post_id');
        
        $this->hasMany('tags', 'tags', [
            ['post_tags', 'post_id', 'tag_id']
        ]);

        $this->hasMany('pets', 'pets', [
            ['post_pets', 'post_id', 'pet_id'],
        ]);
    }

    public function create(Connection $conn, Request $body, Response $res): Response
    {
        $acct = $body['data'];
        $required = ['email', 'username', 'firstName', 'lastName', 'password'];
        $keys = array_keys($acct);
        $diff = array_diff($keys, $required);

        // Can't continue if there isn't enough data to create an account.
        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        try {

            $qb = $conn->createQueryBuilder();

            $qb->insert('users')
                ->values([
                    'email'      => $qb->createNamedParameter($acct['email']),
                    'username'   => $qb->createNamedParameter($acct['username']),
                    'first_name' => $qb->createNamedParameter($acct['firstName']),
                    'last_name'  => $qb->createNamedParameter($acct['lastName']),
                    'created_at' => date('c'),
                    'idp_code'   => Idp::THEPETPARK,
                ])
                ->execute();

            $acct['id'] = $this->conn->lastInsertId();
            $password = password_hash($acct['password'], PASSWORD_BCRYPT);
    
            $qb = $this->conn->createQueryBuilder();
            
            $qb->insert('user_passwords')
                ->values([
                    'id' => $qb->createNamedParameter($acct['id'], ParameterType::INTEGER),
                    'passwd' => $qb->createNamedParameter($password),
                ])
                ->execute();
            
        } catch (Exception $e) {

            // E-mail and username have a unique key constraint.
            // Query will fail if provided e-mail and/or username are
            // already associated with an account. Client apps should
            // GET /users to check if an account exists before trying
            // to create a new one.

            return $res->withStatus(409);

        }

        return $res->withStatus(201);
    }
}
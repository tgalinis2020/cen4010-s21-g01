<?php

declare(strict_types=1);

namespace ThePetPark\Repositories;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

use function date;
use function strlen;

final class PostRepository
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function getPosts(): QueryBuilder
    {
        return $this->conn->createQueryBuilder()
            ->select('p.id', 'p.image_url as imageUrl', 'l.likes',
                     'p.text_content as textContent')
            ->from('posts', 'p')
            ->join('p', 'likeables', 'l', 'p.likeable_id = l.id');
    }

    public function createPost(
        string $email, string $passwd, string $username, string $firstName,
        string $lastName, string $avatarURL = ''
    ) {
        $fields = [
            ['email', $email],
            ['username', $username],
            ['first_name', $firstName],
            ['last_name', $lastName],
            ['avatar_url', strlen($avatarURL) == 0 ? null : $avatarURL],
            ['created_at', date('c')],
        ];

        $nfields = count($fields);

        $builder = $this->conn->createQueryBuilder()->insert('users');

        for ($i = 0; $i < $nfields; ++$i) {
            list($field, $value) = $fields[$i];

            $builder->setValue($field, '?')->setParameter($i, $value);
        }

        $builder->execute();

        $id = $this->conn->lastInsertId();

        $this->conn->createQueryBuilder()
            ->insert('user_passwords')
            ->setValue('id', '?')
            ->setValue('passwd', '?')
            ->setParameter(0, $id)
            ->setParameter(1, $passwd)
            ->execute();
    }
}
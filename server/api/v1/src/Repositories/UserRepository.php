<?php

declare(strict_types=1);

namespace ThePetPark\Repositories;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;

use function date;

final class UserRepository
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function getUsersWithPassword(): QueryBuilder
    {
        return $this->conn->createQueryBuilder()
            ->select('u.id', 'u.first_name AS firstName',
                     'u.last_name AS lastName', 'u.avatar_url AS avatar',
                     'u.created_at AS createdAt', 'p.passwd AS password')
            ->from('users', 'u')
            ->join('u', 'user_passwords', 'p', 'p.id = u.id');
    }

    public function getUsers(): QueryBuilder
    {
        return $this->conn->createQueryBuilder()
            ->select('id', 'first_name as firstName', 'last_name as lastName',
                     'username', 'avatar_url as avatar', 'email',
                     'created_at AS createdAt')
            ->from('users', 'u');
    }

    public function setUserAvatar(string $id)
    {
        // stub
    }

    /**
     * Note: although users can have avatars, only authenticated users can
     * upload files. Therefore, brand-new user accounts cannot pick an avatar
     * on registration.
     */
    public function createUser(
        string $email, string $passwd, string $username, string $firstName,
        string $lastName
    ) {
        $count = (int) $this->conn->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('users')
            ->where('email = ?')
            ->orWhere('username = ?')
            ->setParameter(0, $email)
            ->setParameter(1, $username)
            ->execute()
            ->fetchColumn(0);

        if ($count > 0) {
            throw new Exception('User is already registered in the database');
        }

        $fields = [
            ['email', $email],
            ['username', $username],
            ['first_name', $firstName],
            ['last_name', $lastName],
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
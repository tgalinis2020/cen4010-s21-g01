<?php

declare(strict_types=1);

namespace ThePetPark\Repositories;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

use function strlen;

final class PetRepository
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function getPets(): QueryBuilder
    {
        return $this->conn->createQueryBuilder()
            ->select('p.id', 'p.pet_name AS name', 'pt.pet_type AS type',
                    'pb.pet_breed AS breed', 'p.avatar_url AS avatarUrl',
                    'p.pet_description AS description')
            ->from('pets', 'p')
            ->join('p', 'pet_types',  'pt', 'p.pet_type_id = pt.id')
            ->join('p', 'pet_breeds', 'pb', 'p.pet_breed_id = pb.id');
    }

    public function createPet(
        string $ownerID, string $name, string $description, string $type,
        string $breed, string $avatarURL = ''
    ) {
        // Get pet type and breed IDs. If they don't exist, create new entries.
        $petTypeID = $this->conn->createQueryBuilder()
            ->select('id')
            ->from('pet_types')
            ->where('pet_type = ?')
            ->setParameter(0, strtolower($type))
            ->execute()
            ->fetchColumn(0);

        $petBreedID = $this->conn->createQueryBuilder()
            ->select('id')
            ->from('pet_breeds')
            ->where('pet_breed = ?')
            ->setParameter(0, strtolower($breed))
            ->execute()
            ->fetchColumn(0);

        if ($petTypeID === false) {
            $this->conn->createQueryBuilder()
                ->insert('pet_types')
                ->setValue('pet_type', '?')
                ->setParameter(0, $type)
                ->execute();
            
            $petTypeID = $this->conn->lastInsertId();
        }

        if ($petBreedID === false) {
            $this->conn->createQueryBuilder()
                ->insert('pet_breeds')
                ->setValue('pet_breed', '?')
                ->setParameter(0, $breed)
                ->execute();
            
            $petBreedID = $this->conn->lastInsertId();
        }

        $petBreedID = null;

        $fields = [
            ['user_id', $ownerID],
            ['pet_name', $name],
            ['pet_description', $description],
            ['pet_type_id', $petTypeID],
            ['pet_breed_id', $petBreedID],
            ['avatar_url', strlen($avatarURL) == 0 ? null : $avatarURL],
        ];

        $nfields = count($fields);

        $builder = $this->conn->createQueryBuilder()->insert('pets');

        for ($i = 0; $i < $nfields; ++$i) {
            list($field, $value) = $fields[$i];

            $builder->setValue($field, '?')->setParameter($i, $value);
        }

        $builder->execute();
    }
}
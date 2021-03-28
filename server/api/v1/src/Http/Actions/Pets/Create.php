<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Pets;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph;
use ThePetPark\Middleware\Session;

use function json_decode;
use function array_diff;
use function array_keys;
use function count;
use function date;
use function strtolower;
use function htmlentities;

class Create implements Graph\ActionInterface
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

        if (isset($document['data'],
                $document['data']['attributes'],
                $document['data']['type']) === false
            || $document['data']['type'] !== 'pets'
        ) {
            return $res->withStatus(400);
        }

        $data = $document['data'];
        $attributes = $data['attributes'];

        $required = ['name', 'type', 'breed'];
        $keys = array_keys($attributes);
        $diff = array_diff($keys, $required);

        if (count($diff) > 0) {
            return $res->withStatus(400);
        }

        $attributes['name'] = htmlentities($attributes['name'], ENT_QUOTES);
        $attributes['type'] = htmlentities(strtolower($attributes['type']), ENT_QUOTES);
        $attributes['breed'] = htmlentities(strtolower($attributes['breed']), ENT_QUOTES);
        
        $qb = $this->conn->createQueryBuilder();

        // BEGIN(check breed existance)
        $breedID = $qb->select('id')->from('pet_breeds')->where($qb->expr()->eq(
            'pet_breed',
            $qb->createNamedParameter($attributes['breed'])
        ))->execute()->fetchColumn(0);

        if ($breedID === null) {
            $qb = $this->conn->createQueryBuilder();

            $qb->insert('pet_breeds')
                ->setValue('pet_breed', $qb->createNamedParameter($attributes['breed']))
                ->execute();
            
            $breedID = $this->conn->lastInsertId();
        }
        // END(check breed existance)

        // BEGIN(check type existance)
        $typeID = $qb->select('id')->from('pet_types')->where($qb->expr()->eq(
            'pet_type',
            $qb->createNamedParameter($attributes['type'])
        ))->execute()->fetchColumn(0);

        if ($typeID === null) {
            $qb = $this->conn->createQueryBuilder();

            $qb->insert('pet_types')
                ->setValue('pet_type', $qb->createNamedParameter($attributes['type']))
                ->execute();
            
            $typeID = $this->conn->lastInsertId();
        }
        // END(check type existence)

        $createdAt = date('c');

        $qb = $this->conn->createQueryBuilder();

        $qb->insert('pets')
            ->setValue('pet_name', $qb->createNamedParameter($attributes['name']))
            ->setValue('pet_type', $qb->createNamedParameter($typeID))
            ->setValue('pet_breed', $qb->createNamedParameter($breedID))
            ->setValue('created_at', $qb->createNamedParameter($createdAt));

        if (isset($attributes['description'])) {
            $attributes['description'] = htmlentities($attributes['description'], ENT_QUOTES);

            $qb->setValue(
                'pet_description',
                $qb->createNamedParameter($attributes['description'])
            );
        }

        if (isset($attributes['avatar'])) {
            $attributes['avatar'] = htmlentities($attributes['avatar'], ENT_QUOTES);

            $qb->setValue(
                'avatar_url',
                $qb->createNamedParameter($attributes['avatar'])
            );
        }

        $qb->execute();

        $petID = $this->conn->lastInsertId();

        $document = [
            'jsonapi' => '1.0',
            'data' => [
                'type' => 'pets',
                'id' => $petID,
                'attributes' => [
                    'name'          => $attributes['name'],
                    'description'   => $attributes['description'] ?? null,
                    'avatar'        => $attributes['avatar'] ?? null,
                    'createdAt'     => $createdAt,
                ],
                'relationships' => [
                    'type'  => ['type'  => 'pet-types',  'id' => $typeID],
                    'breed' => ['breed' => 'pet-breeds', 'id' => $breedID],
                ]
            ],

            'included' => [[
                'type' => 'pet-types',
                'id' => $typeID,
                'attributes' => [
                    'type' => $attributes['type'],
                ]
            ], [
                'type' => 'pet-breeds',
                'id' => $breedID,
                'attributes' => [
                    'breed' => $attributes['breed'],
                ]
            ]]
        ];

        $res->getBody()->write(json_encode($document));

        return $res->withStatus(201);
    }
}
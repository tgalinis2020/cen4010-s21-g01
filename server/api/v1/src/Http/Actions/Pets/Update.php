<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions\Pets;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph;
use ThePetPark\Middleware\Session;

use function json_decode;
use function strtolower;
use function htmlentities;

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
        $session = $req->getAttribute(Session::TOKEN);

        if ($session === null) {
            return $res->withStatus(401);
        }

        $document = json_decode($req->getBody(), true);

        if (isset($document['data'],
                $document['data']['attributes'], $document['data']['type'],
                $document['data']['id']) === false
            || $document['data']['type'] !== 'pets'
        ) {
            return $res->withStatus(400);
        }

        $data = $document['data'];
        $attributes = $data['attributes'];

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

        $qb = $this->conn->createQueryBuilder()->update('pets')->where($qb->expr()->eq(
            'id',
            $qb->createNamedParameter($data['id'])
        ));

        if (isset($attributes['type'])) {
            $attributes['type'] = htmlentities(strtolower($attributes['type']), ENT_QUOTES);

            $sub = $this->conn->createQueryBuilder();

            $typeID = $sub->select('id')->from('pet_types')->where($sub->expr()->eq(
                'pet_type',
                $sub->createNamedParameter($attributes['type'])
            ))->execute()->fetchColumn(0);

            if ($typeID === null) {
                $sub = $this->conn->createQueryBuilder();

                $sub->insert('pet_types')
                    ->setValue('pet_type', $sub->createNamedParameter($attributes['type']))
                    ->execute();

                $typeID = $this->conn->lastInsertId();
            }

            $qb->set(
                'type_id',
                $qb->createNamedParameter($attributes['type'])
            );
        }


        if (isset($attributes['description'])) {
            $attributes['description'] = htmlentities($attributes['description'], ENT_QUOTES);

            $qb->set(
                'pet_description',
                $qb->createNamedParameter($attributes['description'])
            );
        }

        if (isset($attributes['avatar'])) {
            $attributes['avatar'] = htmlentities($attributes['avatar'], ENT_QUOTES);

            $qb->set(
                'avatar_url',
                $qb->createNamedParameter($attributes['avatar'])
            );
        }

        $qb->execute();

        return $res->withStatus(204);
    }
}
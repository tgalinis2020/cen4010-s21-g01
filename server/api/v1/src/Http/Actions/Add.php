<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use Exception;
use ThePetPark\Schema;
use ThePetPark\Schema\Relationship as R;

use function json_encode;
use function json_decode;
use function htmlentities;
use function count;

final class Add
{
    /** @var \ThePetPark\Schema\Container */
    private $schemas;

    /** @var string */
    private $baseUrl;

    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn, Schema\Container $schemas, string $url)
    {
        $this->conn = $conn;
        $this->schemas = $schemas;
        $this->baseUrl = $url;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $input = json_decode((string) $request->getBody(), true);
        $data = $input['data'] ?? null;
        
        if ($data === null) {
            // Body is empty, can't continue.
            return $response->withStatus(400);
        }
        
        $resource = $request->getAttribute('resource');
        $type = $data['type'] ?? null;
        $attributes = $data['attributes'] ?? [];
        $relationships = $data['relationships'] ?? [];

        if ($type === null || empty($attributes) || $type !== $resource) {
            // Input document needs to follow the JSON:API spec.
            return $response->withStatus(400);
        }

        $schema = $this->schemas->get($resource);
        $qb = $this->conn->createQueryBuilder()->insert($schema->getImplType());

        $values = [];
        $present = [];
        
        foreach ($schema->getAttributes() as $attr) {
            $present[$attr] = $attr;
        }

        foreach ($attributes as $attr => $value) {
            if ($schema->hasAttribute($attr) === false) {
                return $response->withStatus(400);
            }

            unset($present[$attr]);

            $values[$attr] = $value ? htmlentities($value) : null;

            $qb->setValue(
                $schema->getImplAttribute($attr),
                $qb->createNamedParameter($values[$attr])
            );
        }

        // If an attribute was not provided, attempt to add a default value.
        foreach ($present as $attr) {
            $default = $schema->getDefaultValueFactory($attr);

            $value = $default === null ? null : $default->get();

            if ($default !== null) {
                $values[$attr] = htmlentities($value);

                $qb->setValue(
                    $schema->getImplAttribute($attr),
                    $qb->createNamedParameter($values[$attr])
                );
            }
        }

        $rels     = [];
        $deferred = [];

        foreach ($relationships as $name => $obj) {
            if ($schema->hasRelationship($name) === false) {
                return $response->withStatus(400);
            }

            $value = $obj['data'];

            list($mask, $related, $link) = $schema->getRelationship($name);

            if (($mask & (R::ONE|R::OWNED)) && is_array($link) === false) {

                if (isset($value['id'], $value['type']) === false || $value['type'] !== $related) {
                    return $response->withStatus(400);
                }
                
                // Immediately apply direct belongs-to-one relationships.
                // In SQL backends, a NOT NULL constraint might be applied.
                $rels[$name] = ['type' => $related, 'id' => htmlentities($value['id'])];
                $qb->setValue($link, $qb->createNamedParameter($rels[$name]['id']));

            } else {

                if ($mask & R::ONE) {
                    $value = [$value];
                }

                // Make sure valid relationships were provided before attempting
                // to make any associations.
                foreach ($value as $identifier) {
                    if (isset($identifier['type'], $identifier['id']) === false) {
                        return $response->withStatus(400);
                    }
                }

                // If the foreign key exists elsewhere, the new resource's ID
                // is required before making the association.
                $deferred[$name] = [$mask, $related, $link, $value];
            
            }
        }

        try {
            $qb->execute();
        } catch (Exception $e) {
            // TODO:    IF proper error reporting is implemented, it would be
            //          great to report what exactly went wrong (i.e.) required
            //          attribute/relationship is missing, etc.
            return $response->withStatus(400);
        }

        $id = $this->conn->lastInsertId();

        // BEGIN(copy-paste from Relationships\Add)
        foreach ($deferred as $name => list($mask, $related, $link, $value)) {

            $n = 0;

            if ($mask & R::MANY) {
                $rels[$name] = [];
            }

            // TODO:    Since to-many resolutions must be done using one table
            //          in order for relationship updates to work, it might be
            //          worth not putting the relationship in an array of one
            //          element.
            if (is_array($link)) {
                $link = array_pop($link);
            }

            foreach ($value as $identifier) {

                $qb = $this->conn->createQueryBuilder();

                $obj = [
                    'type' => $related,
                    'id'   => htmlentities($identifier['id'])
                ];
                
                if ($mask & R::ONE) {
                    $rels[$name] = $obj;
                } else {
                    $rels[$name][$n++] = $obj;
                }
    
                // TODO:    Only single-dimension relationships are supported.
                //          To-many relationships must be resolved using one pivot table.
                //          This is a reasonable limitation for the time being.
                if (is_array($link)) {
    
                    // Adding to-many relationships requires an INSERT query.
                    list($pivot, $from, $to) = $link;
    
                    $qb->insert($pivot)
                        ->setValue($from, $qb->createNamedParameter($id))
                        ->setValue($to, $qb->createNamedParameter($obj['id']));
    
                } else {
    
                    // Need to know where the foreign key exists in direct to-many
                    // relationships. If the resource owns the related resource,
                    // the foreign key exists in the related resource.
                    $target = ($mask & R::OWNS) ? $related : $resource;
    
                    // Adding to-one relationships requires an UPDATE query.
                    $schema = $this->schemas->get($target);
    
                    $qb->update($schema->getImplType())
                        ->set($link, $qb->createNamedParameter($obj['id']))
                        ->where($qb->expr()->eq($link, $qb->createNamedParameter($id)));
                }
    
                $qb->execute();
            }

        }
        // END(copy-paste from Relationships\Add)

        $document = [
            'jsonapi' => '1.0',
            'links' => [
                'self' => $this->baseUrl . '/' . $resource . '/' . $id,
            ],
            'data' => [
                'id'         => $id,
                'type'       => $resource,
                'attributes' => $values,
            ],
        ];

        if (count($rels) > 0) {
            $document['data']['relationships'] = $rels;
        }

        $response->getBody()->write(json_encode($document));

        return $response->withStatus(201);
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;

/**
 * Resource schema container.
 * 
 * Uses DBAL's QueryBuilder to create queries based on the structure of the
 * request.
 * 
 * @author Thomas Galinis <tgalinis2020@fau.edu>
 */
class Graph
{
    // These constants hold request attribute keys that must exist in the
    // request. An adapter must take care of mapping them to routes.
    const RESOURCE_TYPE      = 'graph_type';
    const RESOURCE_ID        = 'graph_id';
    const RELATIONSHIP_TYPE  = 'graph_relationship';
    const ID_REGEX           = '[0-9]+';
    const VERB_MAP = [
        'POST'   => 'create',
        'PUT'    => 'replace',
        'PATCH'  => 'update',
        'DELETE' => 'delete',
    ];

    /** @var Schema[] */
    private $schemas;

    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function addDefinitions(string $filename)
    {
        foreach ((require $filename) as $cls) {
            $this->add(new $cls);
        }
    }

    public function get(string $resource)
    {
        return $this->schemas[$resource] ?? null;
    }

    public function add(Schema $schema)
    {
        $schema->bootstrap();
        $this->schemas[$schema->getType()] = $schema;
    }

    public function mutateRelationship(Request $req, Response $res): Response
    {
        return $res->withStatus(501);
    }

    public function resolveRelationship(Request $req, Response $res): Response
    {
        return $res->withStatus(501);
    }

    public function mutate(Request $req, Response $res): Response
    {
        $type = $req->getAttribute(Graph::RESOURCE_TYPE);
        $schema = $this->get($type);

        if ($schema === null) {
            return $res->withStatus(404);
        }

        return call_user_func([$schema, Graph::VERB_MAP[$req->getMethod()]], $this->conn, $req, $res);
    }

    /**
     * Generates a query using the information provided in the request's
     * attributes. Returns a JSON-API document.
     */
    public function resolve(Request $req, Response $res): Response
    {
        parse_str($req->getUri()->getQuery(), $params);

        $type = $req->getAttribute(Graph::RESOURCE_TYPE);
        $resourceID = $req->getAttribute(Graph::RESOURCE_ID);
        $relationship = $req->getAttribute(Graph::RELATIONSHIP_TYPE);

        // Assume fetching a collection
        $amount = Schema::MANY;
        
        $qb = $this->conn->createQueryBuilder()->select();
        $conditions = $qb->expr()->andX();
        $resource = $this->get($type);
        $size = 20;
        
        
        // Relationship-to-enumeration map. Root element has no
        // relationship, therefore its key is an empty string.
        // Enumerations are prefixed with 't' since MySQL aliases cannot
        // begin with a number.
        $ref = ['' => $this->getRef()];

        // The following are parallel arrays indexed by a reference value
        // from $ref
        $map = []; // ref-to-resource
        $raw = []; // ref-to-raw data
        $dat = []; // ref-to-transformed data
        $rel = []; // ref-to-relationships

        $resource->initialize($qb, $ref['']);

        if ($resourceID !== null) {

            $conditions->add($qb->expr()->eq(
                $ref[''] . '.' . $resource->getId(),
                $qb->createNamedParameter($resourceID)
            ));

            if ($relationship !== null) {

                $ref[$relationship] = $this->newRef();
                
                list($related, $mask) = $resource->resolve(
                    $this->graph,
                    $qb,
                    $relationship,
                    $ref[''],
                    $ref[$relationship]
                );

                $res[$this->getRef()] = $related;
    
                $related->includeFields($qb, $ref[$relationship]);

                if ($mask & Schema::ONE) {
                    $amount = Schema::ONE;
                }

            } else {

                $amount = Schema::ONE;

                $resource->includeFields($qb, $ref['']);
            }
        
        }

        if (isset($params['page']) && ($amount === Schema::MANY)) {

            $size = (int) ($params['page']['size'] ?? $size);

            if (isset($params['cursor'])) {
                $conditions->add($qb->expr()->gt(
                    $ref[''] . '.' . $resource->getId(),
                    $qb->createNamedParameter($params['cursor'])
                ));
            }

        }

        /*
        $raw[$this->getRef()] = $qb->where($conditions)
            ->execute()
            ->fetchAll(FetchMode::ASSOCIATIVE);
        */

        // TODO: remove prefix from results
        
        // TODO: propagate relationships to parent, if applicable
        // (might be able to do this while removing prefixes)

        $dat = [$this->getRef() => [
            ['id' =>  0],
            ['id' => $size-1],
        ]];
        $resolved = $dat[$this->getRef()];
        $rowCount = count($resolved);

        if ($rowCount > 0 && isset($params['include'])) {

            // Reset fields and conditions but keep source table(s).
            // This will be a new query based on retrieved data.
            $qb->select();
            $conditions = $qb->expr()->andX();

            if ($rowCount > 1) {
                // No need to constrain the second query any further since
                // there's only one result.
                $start = $resolved[0]['id'];
                $end = $resolved[$rowCount - 1]['id'];

                // Only include data relevant to the previously fetched data.
                $conditions->add($qb->expr()->andX(
                    $qb->expr()->gte($this->getRef() . 'id', $start),
                    $qb->expr()->lte($this->getRef() . 'id', $end)
                ));
            }

            // TODO: parse includes
        }

        // TODO: apply query filters (and resolve relationships as needed)

        // TODO: apply sorting

        // (optional) TODO: apply sparse fields

        // TODO: serialize raw data and relationships to a JSONAPI document

        // See if the expected query is generated.
        $res->getBody()->write($qb->getSQL());

        return $res
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus(200);
    }

    private function getRef(): string
    {
        return 't' . $this->refcount;
    }

    private function newRef(): string
    {
        return 't' . (++$this->refcount);
    }
}

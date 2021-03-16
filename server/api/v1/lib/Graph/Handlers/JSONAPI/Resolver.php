<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Handlers\JSONAPI\Resource;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use ThePetPark\Library\Graph\Graph;
use ThePetPark\Library\Graph\ActionInterface;


use ThePetPark\Library\Graph\Relationship as R;

use function explode;
use function is_numeric;

/**
 * Generates a query using the information provided in the request's
 * attributes. Returns a JSON-API document.
 */
class Resolver implements ActionInterface
{
    /** @var int */
    private $refcount = 0;

    private function getRef(): string
    {
        return 't' . $this->refcount;
    }

    private function newRef(): string
    {
        return 't' . (++$this->refcount);
    }
    
    public function execute(
        Graph $graph,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        parse_str($request->getUri()->getQuery(), $params);

        $type = $request->getAttribute(Graph::RESOURCE_TYPE);
        $resourceID = $request->getAttribute(Graph::RESOURCE_ID);
        $relationship = $request->getAttribute(Graph::RELATIONSHIP_TYPE);

        $conn = $graph->getConnection();
        $qb = $conn->createQueryBuilder();
        $conditions = $qb->expr()->andX();
        $resource = $graph->get($type);
        $size = $graph->getDefaultPageSize();
        $amount = R::MANY; // Assume fetching a collection.        
        
        
        // Relationship-to-enumeration map. Root element has no
        // relationship, therefore its key is an empty string.
        // Enumerations are prefixed with 't' since MySQL aliases cannot
        // begin with a number.
        $ref = ['' => $this->getRef()];

        // The following are parallel arrays indexed by a reference value
        // from $ref
        $map = [$ref[''] => $resource]; // ref-to-resource
        $raw = [];                      // ref-to-raw data
        $dat = [];                      // ref-to-transformed data
        $rel = [];                      // ref-to-relationships

        $resource->initialize($qb, $ref['']);

        if ($resourceID !== null) {

            $conditions->add($qb->expr()->eq(
                $ref[''] . '.' . $resource->getId(),
                $qb->createNamedParameter($resourceID)
            ));

            if ($relationship !== null) {

                $ref[$relationship] = $this->newRef();
                
                $related = $resource->resolve(
                    $this->graph,
                    $qb,
                    $relationship,
                    $ref[''],
                    $ref[$relationship]
                );

                $res[$this->getRef()] = $related->getSchema();
    
                $related->getSchema()->includeFields($qb, $ref[$relationship]);

                if ($related->getType() & R::ONE) {
                    $amount = R::ONE;
                }

            } else {

                $amount = R::ONE;

                $resource->includeFields($qb, $ref['']);
            }
        
        } else {

            $resource->includeFields($qb, $ref['']);

        }

        if (isset($params['page']) && ($amount === R::MANY)) {

            $page = $params['page'];

            if (isset($page['size']) && is_numeric($page['size'])) {
                $size = (int) ($page['size'] ?? $size);
            }

            if (isset($page['cursor'])) {
                $conditions->add($qb->expr()->gt(
                    $ref[''] . '.' . $resource->getId(),
                    $qb->createNamedParameter($page['cursor'])
                ));
            }

        }

        $qb->setMaxResults($size);

        // TODO: parse filters, defer filters that do not affect main query.

        $mainSQL = $qb->getSQL();

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
            // Create a new query based on retrieved data.
            $qb->resetQueryPart('select');
            $qb->setMaxResults(null);
            $conditions = $qb->expr()->andX();

            foreach (explode(',', $params['include']) as $included) {
                $cursor = $resource;
                $parentRef = $ref[''];
                $tokens = explode('.', $included);
                $token = $tokens[0];
                $delim = '';
                
                foreach ($tokens as $r) {
                    $token .= $delim . $r;

                    if (!isset($ref[$token])) {
                        
                        if ($cursor->hasRelationship($r) === false) {

                            // If provided token isn't a valid relationship,
                            // stop here.
                            return $res->withStatus(400);
                        
                        }

                        $ref[$token] = $this->newRef();
                        list($related, $mask) = $cursor->resolve($graph, $qb, $r,
                                $parentRef, $ref[$token]);
                        $parentRef = $ref[$token];
                        $cursor = $related;
                        $cursor->includeFields($qb, $ref[$token]);
                        $map[$ref[$token]] = $cursor;
                    }

                    $delim = '.';
                }
            }

            // No need to constrain the second query any further if there's
            // only one result.
            if ($rowCount > 1) {

                $idField = $ref[''] . '.' . $map[$ref['']]->getId();

                // Only include data relevant to the previously fetched data.
                $conditions->add($qb->expr()->andX(
                    $qb->expr()->gte($idField, $resolved[0]['id']),
                    $qb->expr()->lte($idField, $resolved[$rowCount - 1]['id'])
                ));

            }

            // TODO: parse includes
        }

        // TODO: apply query filters (and resolve relationships as needed)

        // TODO: apply sorting

        $qb->where($conditions);

        // (optional) TODO: apply sparse fields

        // TODO: serialize raw data and relationships to a JSONAPI document

        // See if the expected query is generated.
        $includesSQL = $qb->getSQL();

        $res->getBody()->write("Query 1:\n$mainSQL\n\nQuery 2:\n$includesSQL");

        return $res
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus(200);
    }
}
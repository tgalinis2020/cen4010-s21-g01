<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Actions;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use ThePetPark\Library\Graph\Graph;
use ThePetPark\Library\Graph\ActionInterface;
use ThePetPark\Library\Graph\Relationship as R;

use Exception;
use ThePetPark\Library\Graph\ReferenceTable;

use function explode;
use function count;
use function is_numeric;

/**
 * Generates a query using the information provided in the request's
 * attributes. Returns a JSON-API document.
 */
class Resolve implements ActionInterface
{
    public function execute(
        Graph $graph,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {

        parse_str($request->getUri()->getQuery(), $params);

        $type = $request->getAttribute(Graph::PARAM_RESOURCE);
        $resourceID = $request->getAttribute(Graph::PARAM_ID);
        $relationship = $request->getAttribute(Graph::PARAM_RELATIONSHIP);

        $conn = $graph->getConnection();
        $qb = $conn->createQueryBuilder();
        $conditions = $qb->expr()->andX();
        $qb->where($conditions);

        // Initialize sparse fieldsets. They are attributes to select, indexed
        // by resource type.
        $sparseFields = [];

        foreach (($params['fields'] ?? []) as $resourceType => $fieldList) {
            if (($resource = $graph->get($resourceType)) !== null) {
                // Silently ignore invalid types
                $sparseFields[$resourceType] = [];

                foreach (explode(',', $fieldList) as $attr) {
                    if ($resource->hasAttribute($attr)) {
                        $sparseFields[$resourceType][] = $attr;
                    } else {
                        // TODO: silently ignore or send a 400?
                        // TODO: might be worth deferring error handling
                        // to a user-defined handler. Maybe the resolver
                        // could provide a reason and the handler can perform
                        // an action based on the given reason.
                    }
                }
            }
        }

        $resource   = $graph->get($type);
        $amount     = R::MANY; // Assume fetching a collection.
        $reftable   = $graph->getReferenceTable();
        $ref        = $reftable->getLatestRef();

        $resource->initialize($qb, $ref);

        if ($resourceID !== null) {

            $conditions->add($qb->expr()->eq(
                $ref . '.' . $resource->getId(),
                $qb->createNamedParameter($resourceID)
            ));

            if ($relationship !== null) {

                // Create a new reference for the related resource.
                //$ref[$relationship] = $this->newRef();
                $relatedRef = $reftable->newRef($relationship, $ref);

                $relationship = $resource->resolve(
                    $graph,
                    $qb,
                    $relationship,
                    $ref,
                    $relatedRef
                );

                // Select the fields from the related resource.
                //$sel = $relationship;
                $resource = $relationship->getSchema();

                $reftable->setResource($relatedRef, $resource);

                $reftable->pushRef($relatedRef);

                if ($relationship->getType() & R::ONE) {
                    $amount = R::ONE;
                }

            } else {

                $amount = R::ONE;
            }
        
        }

        $resource->includeFields($qb, $reftable->getBaseRef(), $sparseFields);

        if ($amount === R::MANY) {
            $graph->getStrategy('pagination')
                ->apply($graph, $qb, $conditions, $params);
        } else {
            $qb->setMaxResults(1);
        }

        foreach (['filter', 'sort'] as $s) {
            $graph->getStrategy($s)
                ->apply($graph, $qb, $conditions, $params);
        }
        
        $mainSQL = $qb->getSQL();

        /*
        $raw[$this->getRef()] = $qb->where($conditions)
            ->execute()
            ->fetchAll(FetchMode::ASSOCIATIVE);
        */

        // TODO: remove prefix from results
        
        // TODO: propagate relationships to parent, if applicable
        // (might be able to do this while removing prefixes)

        $dat = [$reftable->getBaseRef() => [
            ['id' =>  0],
            ['id' => 100],
        ]];
        $resolved = $dat[$reftable->getBaseRef()];
        $rowCount = count($resolved);

        if ($rowCount > 0 && isset($params['include'])) {

            // Reset fields but keep source table(s) and conditions.
            // Create a new query based on retrieved data.
            $qb->resetQueryPart('select');
            $qb->setMaxResults(null);

            foreach (explode(',', $params['include']) as $included) {
                $ref = $reftable->getBaseRef();
                $cursor = $graph->getByRef($ref);
                $token = '';
                $delim = '';
                
                foreach (explode('.', $included) as $r) {
                    $token .= $delim . $r;

                    if ($cursor->hasRelationship($r) === false) {

                        // If provided token isn't a valid relationship,
                        // stop here. TODO: might be worth deferring error
                        // handling to another controller.
                        return $response->withStatus(400);
                    
                    }
                    
                    // A resource may have already been resolved (joined in
                    // the query) if it was used in a filter.
                    if ($reftable->hasRefForToken($token)) {

                        $relatedRef = $reftable->getRefByToken($token);
                        $cursor = $graph->getByRef($relatedRef);
                        
                    } else {

                        $relatedRef = $reftable->newRef($token, $ref);
                        $related = $cursor->resolve($graph, $qb, $r,
                            $ref, $relatedRef);
                        $cursor = $related->getSchema();
                        $reftable->setResource($relatedRef, $cursor);

                    }
                    
                    $cursor->includeFields($qb, $relatedRef, $sparseFields);
                    $ref = $relatedRef;

                    $delim = '.';
                }
            }

            // No need to constrain the second query any further if there's
            // only one result.
            if ($rowCount > 1) {
                $baseRef = $reftable->getBaseRef();
                $base = $graph->getByRef($baseRef);
                $idField = $baseRef . '.' . $base->getId();

                // Only include data relevant to the previously fetched data.
                $conditions->add($qb->expr()->andX(
                    $qb->expr()->gte($idField, $resolved[0]['id']),
                    $qb->expr()->lte($idField, $resolved[$rowCount - 1]['id'])
                ));
            }

            // TODO: parse includes
        }

        // TODO: serialize raw data and relationships to a JSONAPI document

        // See if the expected query is generated.
        $includesSQL = $qb->getSQL();

        $response->getBody()->write("Query 1:\n$mainSQL\n\nQuery 2:\n$includesSQL");

        return $response
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus(200);

    }
}
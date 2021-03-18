<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Actions;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Relationship as R;

use function explode;
use function count;

/**
 * Generates a query using the information provided in the request's
 * attributes. Returns a JSON-API document.
 */
class Resolve implements Graph\ActionInterface
{
    public function execute(
        Graph\App $graph,
        ServerRequestInterface $request
    ): ResponseInterface {

        parse_str($request->getUri()->getQuery(), $params);

        $type = $request->getAttribute(Graph\App::PARAM_RESOURCE);
        $resourceID = $request->getAttribute(Graph\App::PARAM_ID);
        $relationship = $request->getAttribute(Graph\App::PARAM_RELATIONSHIP);

        $conn = $graph->getConnection();
        $qb = $conn->createQueryBuilder();
        $response = $graph->createResponse();

        // Initialize sparse fieldsets. They are attributes to select, indexed
        // by resource type.
        $sparseFields = [];
        $data = [];

        foreach (($params['fields'] ?? []) as $resourceType => $fieldList) {
            if (($schema = $graph->getSchema($resourceType)) !== null) {
                // Silently ignore invalid types
                $sparseFields[$resourceType] = [];

                foreach (explode(',', $fieldList) as $attr) {
                    if ($schema->hasAttribute($attr)) {
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

        $baseRef    = $graph->getBaseRef();
        $baseSchema = $graph->getSchema($type);
        $amount     = R::MANY;

        $baseSchema->initialize($qb, $baseRef);

        if ($resourceID !== null) {

            $qb->andWhere($qb->expr()->eq(
                $baseRef . '.' . $baseSchema->getId(),
                $qb->createNamedParameter($resourceID)
            ));

            if ($relationship !== null) {

                // Base the query on related resource.
                $relationship = $baseSchema->resolve($graph, $qb, $baseRef, $relationship);
                $baseSchema = $relationship->getSchema();
                $baseRef = $relationship->getRef();
                $graph->promoteRef($baseRef);

                if ($relationship->getType() & R::ONE) {
                    $amount = R::ONE;
                }

            } else {

                $amount = R::ONE;
            }
        
        }

        $baseSchema->includeFields($qb, $baseRef, $sparseFields);
        $graph->applyFeatures($qb, $params);

        if ($amount === R::ONE) {
            $qb->setMaxResults(1);
        }
        
        $mainSQL = $qb->getSQL();

        //$data[$ref] = $qb->execute()->fetchAll(FetchMode::ASSOCIATIVE);
        $data[$baseRef] = [
            ['id' =>   0],
            ['id' => 100],
        ];

        // TODO: remove prefix from results
        
        // TODO: propagate relationships to parent, if applicable
        // (might be able to do this while removing prefixes)

        
        $resolved = $data[$baseRef];
        $rowCount = count($resolved);

        if ($rowCount > 0 && isset($params['include'])) {

            // Reset fields but keep source table(s) and conditions.
            // Create a new query based on retrieved data.
            $qb->resetQueryPart('select');
            $qb->setMaxResults(null);

            foreach (explode(',', $params['include']) as $included) {
                $ref = $baseRef;
                $schema = $baseSchema;
                $token = '';
                $delim = '';
                
                foreach (explode('.', $included) as $r) {
                    $token .= $delim . $r;

                    if ($schema->hasRelationship($r) === false) {

                        // If provided token isn't a valid relationship,
                        // stop here. TODO: might be worth deferring error
                        // handling to another controller.
                        return $response->withStatus(400);
                    
                    }
                    
                    // A resource may have already been resolved (joined in
                    // the query) if it was used in a filter.
                    if ($graph->hasRefForToken($token)) {

                        $relatedRef = $graph->getRefByToken($token);
                        $schema = $graph->getSchemaByRef($relatedRef);
                        
                    } else {

                        $relationship = $schema->resolve($graph, $qb, $ref, $r);
                        $relatedRef = $relationship->getRef();
                        $schema = $relationship->getSchema();

                    }
                    
                    $schema->includeFields($qb, $relatedRef, $sparseFields);
                    $ref = $relatedRef;

                    $delim = '.';
                }
            }

            // No need to constrain the second query any further if there's
            // only one result.
            if ($rowCount > 1) {
                $idField = $baseRef . '.' . $baseSchema->getId();

                // Only include data relevant to the previously fetched data.
                $qb->andWhere($qb->expr()->andX(
                    $qb->expr()->gte($idField, $resolved[0]['id']),
                    $qb->expr()->lte($idField, $resolved[$rowCount - 1]['id'])
                ));
            }

            // TODO: parse includes
        }

        // TODO: serialize raw data and relationships to a JSONAPI document

        // See if the expected query is generated.
        $includesSQL = isset($params['include']) ? $qb->getSQL() : '(not applicable)';

        $response->getBody()->write("Query 1:\n$mainSQL\n\nQuery 2:\n$includesSQL");

        return $response
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus(200);

    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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
    public function execute(Graph\App $graph, Request $request): Response
    {
        parse_str($request->getUri()->getQuery(), $params);

        // Apply sparse fieldsets, if applicable.
        foreach (($params['fields'] ?? []) as $resourceType => $fieldset) {
            if (($schema = $graph->getSchema($resourceType)) !== null) {

                // Deselect all of the resource's attributes; only apply
                // those that are specified in the sparse fieldset. 
                $schema->clearFields();
                
                // Silently ignore invalid types
                $sparseFields[$resourceType] = [];

                foreach (explode(',', $fieldset) as $attr) {
                    if ($schema->hasAttribute($attr)) {
                        $schema->addField($attr);
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

        $type = $request->getAttribute(Graph\App::PARAM_RESOURCE);
        $resourceID = $request->getAttribute(Graph\App::PARAM_ID);
        $relationship = $request->getAttribute(Graph\App::PARAM_RELATIONSHIP);

        $response = $graph->createResponse();

        // Initialize sparse fieldsets. They are attributes to select, indexed
        // by resource type.
        $sparseFields = [];
        $data = [];

        

        list($baseSchema, $baseRef) = $graph->init($type);
        $amount = R::MANY;
        $qb = $graph->getQueryBuilder();

        if ($resourceID !== null) {

            $qb->andWhere($qb->expr()->eq(
                $baseRef . '.' . $baseSchema->getId(),
                $qb->createNamedParameter($resourceID)
            ));

            if ($relationship !== null) {

                // Base the query on related resource.
                //list($baseSchema, $baseRef, $mask) = $graph->resolve($relationship, $baseRef, $qb);
                
                // Promote relationship to the context of the query.
                list($baseSchema, $baseRef, $mask) = $graph->promote($relationship);

                if ($mask & R::ONE) {
                    $amount = R::ONE;
                }

            } else {

                $amount = R::ONE;
            }
        
        }

        $graph->prepare($baseSchema, $baseRef);
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
            $qb->resetQueryParts(['select', 'distinct', 'orderBy']);
            $qb->setMaxResults(null);

            foreach (explode(',', $params['include']) as $included) {
                $ref = $baseRef;
                $schema = $baseSchema;
                $token = '';
                $delim = '';
                
                foreach (explode('.', $included) as $rel) {
                    $token .= $delim . $rel;
                    $relatedRef = null;

                    if ($schema->hasRelationship($rel) === false) {

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

                        list($schema, $relatedRef, $mask) = $graph->resolve($rel, $ref);
                        
                    }
                    
                    $graph->prepareIncluded($schema, $relatedRef, $ref);

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

            /*
            foreach ($qb->execute()->fetchAll() as $record) {
                $graph->scanIncluded($record);
            }
            //*/
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
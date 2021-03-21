<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Schema\Relationship as R;

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
        $qb = $graph->getQueryBuilder();
        $data = [];
        $amount = R::MANY;

        $base = $graph->init($type);

        $qb->select()->distinct()
            ->from($base->getSchema()->getImplType(), $base->getRef());
        

        if ($resourceID !== null) {

            //$driver->select($resourceID);
            $qb->andWhere($qb->expr()->eq(
                $base->getRef() . '.' . $base->getSchema()->getId(),
                $qb->createNamedParameter($resourceID)
            ));

            if ($relationship !== null) {

                // Base the query on related resource.
                //list($baseSchema, $baseRef, $mask) = $graph->resolve($relationship, $baseRef, $qb);
                
                // Promote relationship to the context of the query.
                $base = $graph->resolve($relationship, $base/*, $driver*/);
                
                // baseRef now points to the relationship's reference.
                // Set it as the new context of the query.
                $graph->setBaseRef($base);

                if ($base->getType() & R::ONE) {
                    $amount = R::ONE;
                }

            } else {

                $amount = R::ONE;
            }
        
        }

        $graph->prepare($base);

        foreach ($graph->getFeatures() as $feature) {
            $feat = new $feature;

            if ($feat->check($params)) {
                $feat->apply($graph, $params);
                $feat->clean($params);
            }
        }

        if ($amount === R::ONE) {
            $qb->setMaxResults(1);
        }
        
        $mainSQL = $qb->getSQL();

        //$data[$ref] = $qb->execute()->fetchAll(FetchMode::ASSOCIATIVE);
        //$data[$ref] = $reftable->getData($driver);
        $data[$base->getRef()] = [['id' =>   0], ['id' => 100]];
        
        $resolved = $data[$base->getRef()];
        $rowCount = count($resolved);

        if ($rowCount > 0 && isset($params['include'])) {

            // Reset fields but keep source table(s) and conditions.
            // Create a new query based on retrieved data.
            $qb->resetQueryParts(['select', 'distinct', 'orderBy']);
            $qb->setMaxResults(null);
            //$driver->reset();

            foreach (explode(',', $params['include']) as $included) {
                $ref = $base;
                $token = '';
                $delim = '';
                
                foreach (explode('.', $included) as $relationship) {
                    $token .= $delim . $relationship;
                    $relatedRef = null;

                    if ($ref->getSchema()->hasRelationship($relationship) === false) {

                        // If provided token isn't a valid relationship,
                        // stop here. TODO: might be worth deferring error
                        // handling to another controller.
                        return $response->withStatus(400);
                    
                    }
    
                    // A resource may have already been resolved (joined in
                    // the query) if it was used in a filter.
                    $relatedRef = $graph->hasRefForToken($token)
                        ? $graph->getRefByToken($token)
                        : $graph->resolve($relationship, $ref/*, $driver*/);
            
                    $graph->prepareIncluded($relatedRef, $ref);
                    //$reftable->setParentRef($relatedRef, $ref);
                    //$driver->prepareIncluded($relatedRef, $ref);

                    $ref = $relatedRef;
                    $delim = '.';
                }
            }
            
            //$reftable->getIncluded($driver);

            // No need to constrain the second query any further if there's
            // only one result.
            if ($rowCount > 1) {
                $idField = $base->getRef() . '.' . $base->getSchema()->getId();

                // Only include data relevant to the previously fetched data.
                $qb->andWhere($qb->expr()->andX(
                    $qb->expr()->gte($idField, $resolved[0]['id']),
                    $qb->expr()->lte($idField, $resolved[$rowCount - 1]['id'])
                ));
            }
        }

        // TODO: serialize raw data and relationships to a JSONAPI document

        // See if the expected query is generated.
        $includesSQL = isset($params['include'])
            ? $qb->getSQL()
            : '(not applicable)';

        $response->getBody()->write("Query 1:\n$mainSQL\n\nQuery 2:\n$includesSQL");

        return $response
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus(200);
    }
}
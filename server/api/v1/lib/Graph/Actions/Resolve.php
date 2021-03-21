<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Schema\ReferenceTable;
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

        $schemas = $graph->getSchemas();
        $driver = $graph->getDriver();
        $response = $graph->createResponse();
        $refs = new ReferenceTable($schemas);
        $data = [];
        $amount = R::MANY;

        // Apply sparse fieldsets, if applicable.
        foreach (($params['fields'] ?? []) as $type => $fieldset) {
            if ($schemas->has($type)) {
                $schema = $schemas->get($type);

                // Deselect all of the resource's attributes; only apply
                // those that are specified in the sparse fieldset. 
                $schema->clearFields();
                
                // Silently ignore invalid types
                $sparseFields[$type] = [];

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

        $base = $refs->init($type, $driver);        
        $driver->apply($params, $refs);

        if ($resourceID !== null) {

            $driver->select($base, $resourceID);

            if ($relationship !== null) {
                
                // Promote relationship to the context of the query.
                $base = $refs->resolve($relationship, $base, $driver);
                
                // baseRef now points to the relationship's reference.
                // Set it as the new context of the query.
                $refs->setBaseRef($base);

                if ($base->getType() & R::ONE) {
                    $amount = R::ONE;
                }

            } else {

                $amount = R::ONE;
            }
        
        }

        $driver->prepare($base);
        
        $mainSQL = (string) $driver; // Invoke Doctrine\Driver::__toString

        // TODO: serialize data to response document
        /*
        $data = $refs->scan($driver);
        $rowCount = count($data);
        /*/
        $data = [['id' => '0'], ['id' => '100']];
        $rowCount = count($data);
        //*/
        if ($rowCount > 0 && isset($params['include'])) {

            // Reset fields but keep source table(s) and conditions.
            // Create a new query based on retrieved data.
            $driver->reset();

            foreach (explode(',', $params['include']) as $included) {
                $ref = $base;
                $token = $delim = '';
                
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
                    $relatedRef = $refs->has($token)
                        ? $refs->get($token)
                        : $refs->resolve($relationship, $ref, $driver);
            
                    $refs->setParentRef($relatedRef, $ref);
                    $driver->prepare($relatedRef, $ref);

                    $ref = $relatedRef;
                    $delim = '.';
                }
            }

            // No need to constrain the second query any further if there's
            // only one result.
            if ($rowCount > 1) {
                // GOTCHA! If sorting was applied to the query, the first and
                // last rows in the data array may not contain the first and
                // last IDs, respectively. Can't compare IDs here either since
                // they may not always be autoincrementing integers -- UUIDv1
                // is a popular ID scheme as well.
                //
                // In order for IDs to be useful in the first place, they can't
                // be randomly generated. It should be possible to do a linear
                // search through the returned data to pluck out the min and max.
                //
                // TODO: create an ID resolver that can pick out the min and max
                // IDs when sorting by a field other than ID. Otherwise it's OK
                // to use the first and last rows.
                $firstID = $data[0]['id'];
                $lastID = $data[$rowCount - 1]['id'];
                /*
                if ($sortApplied) {
                    list($firstID, $lastID) = $idResolver->resolve($data);
                }
                */
                $driver->setRange($base, $firstID, $lastID);
            }

            //list($included, $relationships) = $reftable->scanIncluded($driver);

            // TODO: include data to response document
        }

        // TODO: serialize raw data and relationships to a JSONAPI document

        $response->getBody()->write(sprintf(
            "Query 1:\n%s\n\nQuery 2:\n%s",
            $mainSQL,
            isset($params['include']) ? (string) $driver : '(not applicable)'
        ));

        return $response
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus(200);
    }
}
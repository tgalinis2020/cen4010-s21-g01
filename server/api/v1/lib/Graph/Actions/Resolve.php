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
use function array_key_first;
use function array_key_last;
use function key;
use function next;
use function current;

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
        $baseUrl = ''; // TODO: need base URL
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

                if ($base->getSchema()->hasRelationship($relationship) === false) {
                    return $response->withStatus(400);
                }
                
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


        // BEGIN(data retrieval and serialization)
        $links = [];
        
        if ($relationship === null) {
            if ($resourceID === null) {
                $links['self'] = sprintf('%s/%s', $baseUrl, $type);
            } else {
                $links['self'] = sprintf('%s/%s/%s', $baseUrl, $type, $resourceID);
            }
        } else {
            $links['self']    = sprintf('%s/%s/%s/%s', $baseUrl, $type, $resourceID, $relationship);
            $links['related'] = sprintf('%s/%s/%s', $baseUrl, $type, $resourceID);
        }

        $document = ['jsonapi' => '1.0', 'links' => $links, 'data' => null];
        $data     = [];
        $items    = []; 
        $type     = $base->getSchema()->getType();
        $attrs    = $base->getSchema()->getAttributes();
        $prefix   = $refs->getBaseRef() . '_';
        $items    = [];

        //*
        foreach ($driver->fetchAll() as $rec) {
            $resourceID = $rec[$prefix . 'id'];
            $item = ['id' => $resourceID, 'type' => $type, 'attributes' => []];

            foreach ($attrs as list($attr, $impl)) {
                $item['attributes'][$attr] = $rec[$prefix . $attr];
            }

            $items[$resourceID] = $item;
        }
        //*/

        $data[$base->getRef()] = $items;
        $rowCount = count($items);
        // END(data retrieval and serialization)

        if ($rowCount > 0 && isset($params['include'])) {

            // Reset fields but keep source table(s), conditions and base IDs.
            // Create a new query based on retrieved data.
            $driver->reset($base);

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
                $end     = end($data[$base->getRef()]);
                $start   = reset($data[$base->getRef()]);
                $firstID = $start['id'];
                $lastID  = $end['id'];

                // If sorting is applied, it won't always be the case that the
                // first ID is in the first record and the last ID is in the
                // last record. Need to do a linear scan to pluck out the min
                // and max.
                // Although IDs *should* be sequential, they could be of any
                // type. An ID comparator must be provided when initializing
                // the Graph.
                if (isset($params['sort'])) {
                    foreach ($data[$base->getRef()] as $rec) {
                        if ($graph->cmp($firstID, $rec['id']) > 0) {
                            $firstID = $rec['id'];
                        } else if ($graph->cmp($lastID, $rec['id']) < 0) {
                            $lastID = $rec['id'];
                        }
                    }
                }

                $driver->setRange($base, $firstID, $lastID);
            }

            
            // TODO:    Like before, data retrieval and serialization is done
            //          at the same time. Even if it might have slight overhead,
            //          it might be worth simply retrieving data first THEN
            //          serialize it into a JSON API document.
            //          This is kind of hard to look at!
            //
            // TODO:    With the current strategy, it is impossible to paginate
            //          included data since all of it is fetched in one big query.
            //          In hindsight, running a query for each relationship
            //          might not incur that much overhead and allows for more
            //          flexibility with how included data is fetched.
            //          Also, a check for duplicates would not be necessary!
            $records = $driver->fetchAll();

            foreach ($refs->getParentRefs() as $refID => $parent) {
                $data[$refID] = [];
                $ref = $refs->getRefById($refID);
                $prefix = $ref . '_';

                foreach ($records as $rec) {
                    $resourceID = $rec[$prefix . 'id'];
                    
                    // Ignore duplicates. There may be many of them!
                    if (isset($data[$refID][$resourceID]) === false) {
                        $schema = $ref->getSchema();
                        $data[$refID][$resourceID] = [
                            'type'          => $schema->getType(),
                            'id'            => $resourceID,
                            'attributes'    => [],
                        ];
                        
                        // Add attributes to resource object.
                        foreach ($schema->getAttributes() as list($attr, $impl)) {
                            $data[$refID][$resourceID]['attributes'][$attr] = $rec[$prefix . $attr];
                        }
                        
                        
                        // BEGIN(relationship processing)
                        $parentID = $rec[$parent . '_id'];
                        $resourceIdentifier = [
                            'type'  => $schema->getType(),
                            'id'    => $resourceID,
                        ];

                        if (isset($data[$parent->getRef()][$parentID]['relationships']) === false) {
                            $data[$parent->getRef()][$parentID]['relationships'] = [];
                        }

                        if (isset($data[$parent->getRef()][$parentID]['relationships'][$ref->getName()]) == false) {
                            $data[$parent->getRef()][$parentID]['relationships'][$ref->getName()] = [
                                // TODO: need base URL
                                'links' => [
                                    'self' => sprintf(
                                        '%s/%s/relationships/%s',
                                        $baseUrl,
                                        $parent->getSchema()->getType(),
                                        $parentID,
                                        $ref->getName()
                                    ),

                                    'related' => sprintf(
                                        '%s/%s/%s',
                                        $baseUrl,
                                        $parent->getSchema()->getType(),
                                        $parentID,
                                        $ref->getName()
                                    ),
                                ],

                                'data' => [],
                            ];
                        }

                        // Propagate relationship to parent.
                        if ($ref->getType() & R::ONE) {
                            $data[$parent->getRef()][$parentID]['relationships'][$ref->getName()]['data']   = $resourceIdentifier;
                        } else {
                            $data[$parent->getRef()][$parentID]['relationships'][$ref->getName()]['data'][] = $resourceIdentifier;
                        }
                        // END(relationship processing)

                    }
                }
            }

            // Point to included data, which should start immediately after the
            // first reference.
            $document['included'] = [];
            $included = next($data); // main data is the first element, skip it

            while ($included !== false) {
                $ref = $refs->getRefById(key($data));

                if ($ref->getType() & R::ONE) {
                    $document['included'][] = current($included);
                } else {
                    foreach ($included as $resource) {
                        $document['included'][] = $resource;
                    }
                }

                $included = next($data);
            }
        }

        $data = reset($data);
        
        if ($amount & R::ONE) {
            $document['data'] = current($data);
        } else {
            // TODO:    Add top-level pagination links.
            //          This depends on the pagination feature!
            //          Is it even worth distinguishing between different
            //          features? Could stick to keeping filters and sorting
            //          robust and hard-coded; keep pagination strategies
            //          variable and let consumers of this library pick and
            //          choose what type of pagination strategy they would like
            //          to support.
            //
            //          For the sake of this project, pagination links can be
            //          derived using returned data so this is not a critical
            //          issue.
            //
            // $document['links']['prev'] = null;
            // $document['links']['next'] = null;
            $document['data'] = [];

            foreach ($data as $resource) {
                $document['data'][] = $resource;
            }
        }
        

        // TODO:    The JSON:API spec does mention to return an error document
        //          with a top-level "error" namespace in the event of an error.
        //          Currently this is omitted.
        $response->getBody()->write(json_encode($document));

        /*
        $response->getBody()->write(sprintf(
            "Query 1:\n%s\n\nQuery 2:\n%s",
            $mainSQL,
            isset($params['include']) ? (string) $driver : '(not applicable)'
        ));

        $response = $response->withHeader('Content-Type', 'text/plain');
        //*/

        return $response;
    }
}
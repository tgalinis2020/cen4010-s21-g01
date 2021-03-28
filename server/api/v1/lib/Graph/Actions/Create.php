<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Actions;

use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Schema\ReferenceTable;
use ThePetPark\Library\Graph\Schema\Relationship as R;

use function parse_str;
use function explode;
use function count;
use function key;
use function next;
use function current;

/**
 * Generates a query using the information provided in the request's
 * attributes. Returns a JSON-API document.
 * 
 * Note: Work in progress.
 */
class Create implements Graph\ActionInterface
{
    /** @var \Doctrine\DBAL\Query\QueryBuilder */
    protected $qb;

    public function execute(Graph\App $graph, Request $request): Response
    {
        $type = $request->getAttribute(Graph\App::PARAM_RESOURCE);
        $resourceID = $request->getAttribute(Graph\App::PARAM_ID);
        $relationship = $request->getAttribute(Graph\App::PARAM_RELATIONSHIP);

        $response = $graph->createResponse();

        if ($relationship !== null) {
            // Relationship endpoints are not yet implemented.
            return $response->withStatus(501);
        }

        if ($resourceID !== null) {
            // Doesn't make sense to create a new resource when the resource
            // already exists.
            // TODO: let the driver validate this, perhaps?
            return $response->withStatus(400);
        }

        //parse_str($request->getUri()->getQuery(), $params);
        
        $document = json_decode($request->getBody(), true);

        if (isset($document['data'], $document['data']['attributes']) === false) {
            return $response->withStatus(400);
        }

        $typeFromDocument = $document['data']['type'];

        if ($type !== $document['data']['type']) {
            // Something is very off if the client submitted a request to an
            return $response->withStatus(400);
        }

        $attributes = $document['data']['attributes'];
        $relationships = $document['data']['relationships'] ?? [];
        $schemas = $graph->getSchemas();
        $baseUrl = $graph->getBaseUrl();

        $schema = $schemas->get($type);
        
        $this->qb->insert($schema->getImplType());
        $values = [];
        
        foreach ($schema->getAttributes() as list($attr, $impl)) {
            if (in_array($attr, $attributes) === false) {
                return $response->withStatus(400);
            }

            $values[$attr] = $this->qb->createNamedParameter($attributes[$attr]);
        }

        foreach ($relationships as $relationship) {
            if ($schema->hasRelationship($relationship) === false) {
                return $response->withStatus(400);
            }

            list($mask, $relatedType, $link) = $schema->getRelationship($relationship);
        }


        $this->qb->values($values);

        

        return $response;
    }
}
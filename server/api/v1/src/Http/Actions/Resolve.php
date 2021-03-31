<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Schema;
use ThePetPark\Schema\ReferenceTable;
use ThePetPark\Schema\Relationship as R;
use ThePetPark\Middleware\Features\Resolver;

use function reset;
use function current;

/**
 * Renders a JSON:API document provided by middleware.
 * 
 * TODO:    Might be a nice to add relationship links for each requested
 *          resource, even if no includes are specified.
 * 
 * TODO:    Should do JSON data serialization here, not while
 *          parsing data/includes.
 */
final class Resolve
{
    public function __invoke(Request $request, Response $response): Response
    {
        $document      = $request->getAttribute(Resolver::DOCUMENT);
        $quantity      = $request->getAttribute(Resolver::QUANTITY);
        $data          = $request->getAttribute(Resolver::DATA);
        $data          = reset($data); // Only care about main data at this point.
        
        if ($quantity & R::ONE) {
            $document['data'] = current($data) ?: null;
        } else {

            // Each resource is indexed by resource ID; don't include the
            // indexing in the final result.
            foreach ($data as $resource) {
                $document['data'][] = $resource;
            }

        }

        // TODO:    The JSON:API spec does mention to return an error document
        //          with a top-level "error" namespace in the event of an error.
        //          Currently this is omitted.
        $response->getBody()->write(json_encode($document));

        return $response;
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Http\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ThePetPark\Schema\Relationship as R;
use ThePetPark\Middleware\Features\Resolver;

use function reset;
use function current;

/**
 * Renders a JSON:API document provided by middleware.
 * 
 * TODO:    Should do JSON data serialization here, not while
 *          parsing data/includes. Currently data is being converted to resource
 *          objects immediately after being fetched from the database.
 */
final class Render
{
    public function __invoke(Request $request, Response $response): Response
    {
        $document      = $request->getAttribute(Resolver::DOCUMENT);
        $quantity      = $request->getAttribute(Resolver::QUANTITY);
        $records       = $request->getAttribute(Resolver::DATA);
        $data          = reset($records); // primary data is the first record set
        
        if ($quantity & R::ONE) {
            $document['data'] = current($data) ?: null;
        } elseif (empty($data)) {
            $document['data'] = [];
        } else {
            // Each resource is indexed by resource ID; don't include the
            // indexing in the final result.
            foreach ($data as $resource) {
                $document['data'][] = $resource;
            }
        }

        // Add included data, if applicable.
        if (($included = next($records)) !== false) {

            // Some included resources may contain duplicate data.
            // The "resolved" map guarantees that no duplicates will be included.
            // 
            // e.g.     Fetching article authors and article comment authors.
            //          An article author can also be a comment author.
            //          Don't include the same person twice!   
            $resolved = [];

            foreach ($data as $id => $resource) {

                // TODO: If results are paginated, some included resources
                // may propagate relationships to data that has not been selected.
                // Checking if the resource's type is set should be sufficent
                // for now.
                if (isset($resource['type'])) {
                    $type = $resource['type'];

                    if (isset($revolved[$type]) === false) {
                        $resolved[$type] = [];
                    }

                    // Primary data is guaranteed to not contain duplicates; a check
                    // is not necessary.
                    $resolved[$type][$id] = true;
                }
    
            }
            
            $document['included'] = [];
               
            while ($included !== false) {
                foreach ($included as $id => $resource) {
                    $type = $resource['type'];

                    if (isset($revolved[$type]) === false) {
                        $resolved[$type] = [];
                    }

                    if (isset($resolved[$type][$id]) === false) {
                        $document['included'][] = $resource;
                        $resolved[$type][$id] = true;
                    }
                }

                $included = next($records);
            }
        }

        // TODO:    The JSON:API spec does mention to return an error document
        //          with a top-level "error" namespace in the event of an error.
        //          Currently this is omitted.
        $response->getBody()->write(json_encode($document));

        return $response;
    }
}
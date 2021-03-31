<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Schema\ReferenceTable;
use ThePetPark\Schema\Relationship as R;

use PDO;

/**
 * The primary feature: fetch the data associated with the given URL.
 */
final class Resolver
{
    /**
     * Request attribute key that should contain the URL query parameters
     * serialized into an array.
     * 
     * @var string
     */
    const PARAMETERS = 'Parameters';

    /**
     * This endpoint generates a JSON:API document in the form of a PHP array.
     * The render middleware handles outputting the final result.
     * 
     * @var string
     */
    const DOCUMENT = 'Document';

    /**
     * Raw data and the amount of records (one or many).
     * 
     * @var string
     */
    const DATA = 'Data';

    
    /**
     * One of the more useful features the JSON:API specification describes
     * is including related resources in one request.
     * 
     * The array contained in this request attribute will contain the 
     * related resource IDs indexed by reference ID.
     * 
     * @var string
     */
    const RELATIONSHIPS = 'Relationships';

    /**
     * Denotes whether fetched data was an item or collection.
     * 
     * @var string
     */
    const QUANTITY = 'Quantity';

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {

        /** @var \Slim\Route */
        $route = $request->getAttribute('route');

        /** @var string */
        $id = $route->getArgument('id');

        /** @var string */
        $relationship = $route->getArgument('relationship');

        /** @var \ThePetPark\Schema\ReferenceTable */
        $refs = $request->getAttribute(ReferenceTable::class);

        /** @var \Doctrine\DBAL\Query\QueryBuilder */
        $qb = $request->getAttribute(QueryBuilder::class);
        
        $data = [];
        $quantity = R::MANY;
        $base = $refs->getBaseRef();

        if ($id !== null) {

            $qb->andWhere($qb->expr()->eq(
                $base . '.' . $base->getSchema()->getId(),
                $qb->createNamedParameter($id)
            ));

            if ($relationship !== null) {

                if ($base->getSchema()->hasRelationship($relationship) === false) {
                    return $response->withStatus(400);
                }
                
                // Promote relationship to the context of the query.
                // Note that applied filters may have already resolved
                // related resources.
                $base = $refs->has($relationship)
                    ? $refs->get($relationship)
                    : $refs->resolve($relationship, $base, $qb);

                if ($base->getType() & R::ONE) {
                    $quantity = R::ONE;
                }

            } else {

                $quantity = R::ONE;
            }
        
        }

        $refs->setBaseRef($base, $qb);

        $data     = [];
        $items    = []; 
        $schema   = $base->getSchema();
        $type     = $schema->getType();
        $attrs    = $schema->getAttributes();
        $prefix   = $base . '_';
        $items    = [];

        foreach ($qb->execute()->fetchAll(PDO::FETCH_ASSOC) as $rec) {
            $resourceID = $rec[$prefix . 'id'];
            $item = ['type' => $type, 'id' => $resourceID, 'attributes' => []];

            foreach ($attrs as $attr) {
                $item['attributes'][$attr] = $rec[$prefix . $attr];
            }

            $items[$resourceID] = $item;
        }
        
        $data[$base->getRef()] = $items;

        return $next(
            $request
                ->withAttribute(self::DATA, $data)
                ->withAttribute(self::QUANTITY, $quantity),
            $response
        );
    }
}
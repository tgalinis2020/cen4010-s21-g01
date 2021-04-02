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
 * 
 * TODO:    Might be useful to add relationship links for each requested
 *          resource, even if no includes are specified.
 */
final class Resolver
{
    /**
     * Request attribute key that should contain the URL query parameters
     * serialized into an array.
     * 
     * @var string
     */
    const PARAMETERS = '__parameters__';

    /**
     * The array representing the output document may be modified by middleware,
     * so it is provided as a request attribute.
     * 
     * @var string
     */
    const DOCUMENT = '__document__';

    /**
     * Raw data indexed by reference ID.
     * 
     * @var string
     */
    const DATA = '__data__';

    /**
     * Denotes whether fetched data was an item or collection.
     * 
     * @var string
     */
    const QUANTITY = '__quantity__';

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
                
                // Promote relationship to the context of the query.
                $base = $refs->resolve($relationship, $base, $qb);

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
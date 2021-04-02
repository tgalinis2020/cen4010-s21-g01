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
 * According to the specification, resources can be included from a relationship
 * endpoint; so what happens if you GET `/articles/1/relationships/comments?include=author`?
 * There's no data to propagate the author relationship to!
 * 
 * Very niche use-case; won't bother with it for the time being.
 */
final class RelationshipResolver
{
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

        $schema = $base->getSchema();

        list($mask, $related, $link) = $schema->getRelationship($relationship);


        return $next(
            $request
                ->withAttribute(Resolver::DATA, $data)
                ->withAttribute(Resolver::QUANTITY, $quantity),
            $response
        );
    }
}
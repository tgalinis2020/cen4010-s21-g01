<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features;

use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ThePetPark\Schema\ReferenceTable;

use function trim;
use function substr;
use function explode;
use function array_pop;

/**
 * Sort main using the fields listed in the "sort" query parameter.
 * 
 * TODO:    The steps to resolving relationships is very similar to
 *          how its done when applying filters. Maybe it's possible to
 *          put shared logic into a driver method.
 */
final class Sorting
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        
        if (isset($params['sort']) === false) {
            return $next($request, $response);
        }

        $qb = $request->getAttribute(QueryBuilder::class);
        $refs = $request->getAttribute(ReferenceTable::class);
        $params = $request->getAttribute(Resolver::PARAMETERS);

        foreach (explode(',', $params['sort']) as $fullyQualifiedField) {
            $ref = $refs->getBaseRef();
            $order = 'ASC';
            $fullyQualifiedField = trim($fullyQualifiedField);

            switch (substr($fullyQualifiedField, 0, 1)) {
            case '-':
                $order = 'DESC';
            case '+':
                $fullyQualifiedField = substr($fullyQualifiedField, 1);
            }

            $tokens = explode('.', $fullyQualifiedField);
            $field = array_pop($tokens);
            $delim = '';
            $token = '';

            foreach ($tokens as $relationship) {
                $token .= $delim . $relationship;

                $ref = $refs->resolve($relationship, $ref, $qb);

                $delim = '.';
            }

            if ($field === 'id') {

                $field = $ref->getSchema()->getId();

            } elseif ($ref->getSchema()->hasAttribute($field)) {

                $field = $ref->getSchema()->getImplAttribute($field);

            } elseif ($ref->getSchema()->hasRelationship($field)) {

                $ref = $refs->resolve($field, $ref, $qb);

                $field = $ref->getSchema()->getId();

            }

            $qb->addOrderBy($ref . '.' . $field, $order);
        }

        return $next($request, $response);

    }
}
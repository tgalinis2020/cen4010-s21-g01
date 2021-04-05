<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features\Pagination;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Middleware\Features\Resolver;
use ThePetPark\Schema\ReferenceTable;

/**
 * This pagination strategy is synonymous with applying a greater-than filter
 * on the provided ID in the cursor. Simple and efficient!
 */
final class CursorBased
{
    /** @var int */
    private $defaultPageSize;

    public function __construct(int $defaultPageSize)
    {
        $this->defaultPageSize = $defaultPageSize;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        $page = $request->getAttribute(Resolver::PARAMETERS, [])['page'] ?? [];

        if (isset($page['cursor'])) {
            $qb = $request->getAttribute(QueryBuilder::class);
            $refs = $request->getAttribute(ReferenceTable::class);
            $ref = $refs->getBaseRef();

            $qb->andWhere($qb->expr()->gt(
                    $ref . '.' . $ref->getSchema()->getId(),
                    $qb->createNamedParameter($page['cursor'])
                ))
                ->setMaxResults((int) ($page['size'] ?? $this->defaultPageSize));             
        }
        
        return $next($request, $response);
    }
}
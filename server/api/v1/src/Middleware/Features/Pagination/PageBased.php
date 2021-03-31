<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features\Pagination;

use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Similar to the offset/limit strategy.
 * Offset is derived from page number and size of the page.
 * Again, not recommended for large sets of data.
 */
final class PageBased
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
        $page = $request->getAttribute('parameters', [])['page'] ?? [];

        if (isset($page['number']) && ((int) $page['number']) > 0) {     
            $page = ((int) $page['number']) - 1; // Pages start at 1
            $size = (int) ($page['size'] ?? $this->defaultPageSize);
    
            $request->getAttribute(QueryBuilder::class)
                ->setFirstResult($page * $size)->setMaxResults($size);
        }

        return $next($request, $response);
    }
}
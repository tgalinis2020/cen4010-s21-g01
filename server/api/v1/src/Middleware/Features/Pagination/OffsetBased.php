<?php

declare(strict_types=1);

namespace ThePetPark\Middleware\Features\Pagination;

use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This pagination strategy applies a numerical offset to the returned records.
 * Not recommended for large amounts of data.
 */
final class OffsetBased
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

        if (isset($page['offset'])) {
            $request->getAttribute(QueryBuilder::class)
                ->setFirstResult((int) $page['offset'])
                ->setMaxResults((int) ($page['limit'] ?? $this->defaultPageSize));
        }
        
        return $next($request, $response);
    }
}
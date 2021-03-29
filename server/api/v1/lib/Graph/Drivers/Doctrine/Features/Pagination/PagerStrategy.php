<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features\Pagination;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph\FeatureInterface;
use ThePetPark\Library\Graph\Schema;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

/**
 * Similar to the offset/limit strategy.
 * Offset is derived from page number and size of the page.
 * Again, not recommended for large sets of data.
 */
class PagerStrategy implements FeatureInterface
{
    /** @var \Doctrine\DBAL\Query\QueryBuilder */
    protected $qb;

    /** @var int */
    protected $defaultPageSize;

    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
        $this->defaultPageSize = 12; // TODO: need to get this from somewhere
    }

    public function provides(): string
    {
        return 'page';
    }

    public function apply(array $params, Schema\Container $schemas, ReferenceTable $refs): bool
    {
        $page = $params['page'];

        if (isset($page['number']) === false || ((int) $page['number']) < 1) {
            return false;
        }

        $page = ((int) $page['number']) - 1; // Pages start at 1
        $size = (int) ($page['size'] ?? $this->defaultPageSize);

        $this->qb->setFirstResult($page * $size)->setMaxResults($size);

        return true;
    }
}
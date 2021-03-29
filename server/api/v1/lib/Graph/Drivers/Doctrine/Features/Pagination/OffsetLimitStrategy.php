<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features\Pagination;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph\FeatureInterface;
use ThePetPark\Library\Graph\Schema;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

/**
 * This pagination strategy applies a numerical offset to the returned records.
 * Not recommended for large amounts of data.
 */
class OffsetLimitStrategy implements FeatureInterface
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

        if (isset($page['offset']) === false) {
            return false;
        }

        $this->qb
            ->setFirstResult((int) $page['offset'])
            ->setMaxResults((int) ($page['limit'] ?? $this->defaultPageSize));
        
        return true;
    }
}
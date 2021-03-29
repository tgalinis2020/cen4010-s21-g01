<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features\Pagination;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph\FeatureInterface;
use ThePetPark\Library\Graph\Schema;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

/**
 * This pagination strategy is synonymous with applying a greater-than filter
 * on the provided ID in the cursor. Simple and efficient!
 */
class CursorStrategy implements FeatureInterface
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

        if (isset($page['cursor']) === false) {
            return false;
        }

        $ref = $refs->getBaseRef();

        $this->qb
            ->andWhere($this->qb->expr()->gt(
                $ref . '.' . $ref->getSchema()->getId(),
                $this->qb->createNamedParameter($page['cursor'])
            ))
            ->setMaxResults((int) ($page['size'] ?? $this->defaultPageSize));
        
        return true;
    }
}
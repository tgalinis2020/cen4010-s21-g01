<?php

declare(strict_types=1);

namespace ThePetPark\Filters;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\FilterInterface;

class GreaterThanOrEqualTo implements FilterInterface
{
    public function apply(QueryBuilder $qb, string $field, $value)
    {
        $qb->andWhere($qb->expr()->gte($field, $qb->createNamedParameter($value)));
    }
}
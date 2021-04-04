<?php

declare(strict_types=1);

namespace ThePetPark\Filters;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\FilterInterface;

class GreaterThan implements FilterInterface
{
    public function apply(QueryBuilder $qb, string $field, $value)
    {
        $qb->andWhere($qb->expr()->gt($field, $qb->createNamedParameter($value)));
    }
}
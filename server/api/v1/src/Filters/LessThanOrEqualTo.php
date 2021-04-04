<?php

declare(strict_types=1);

namespace ThePetPark\Filters;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\FilterInterface;

class LessThanOrEqualTo implements FilterInterface
{
    public function apply(QueryBuilder $qb, string $field, $value)
    {
        $qb->andWhere($qb->expr()->lte($field, $qb->createNamedParameter($value)));
    }
}
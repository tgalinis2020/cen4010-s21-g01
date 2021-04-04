<?php

declare(strict_types=1);

namespace ThePetPark\Filters;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\FilterInterface;

use function explode;

class In implements FilterInterface
{
    public function apply(QueryBuilder $qb, string $field, $value)
    {
        $values = [];

        foreach (explode(',', $value) as $val) {
            $values[] = $qb->createNamedParameter($val);
        }

        $qb->andWhere($qb->expr()->in($field, $values));
    }
}
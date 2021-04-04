<?php

declare(strict_types=1);

namespace ThePetPark\Filters;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\FilterInterface;

use function explode;

class NotLike implements FilterInterface
{
    public function apply(QueryBuilder $qb, string $field, $value)
    {
        $conditions = $qb->expr()->andX();

        foreach (explode(',', $value) as $val) {
            $conditions->add($qb->expr()->notLike($field, $qb->createNamedParameter($val)));
        }
        
        $qb->andWhere($conditions);
    }
}
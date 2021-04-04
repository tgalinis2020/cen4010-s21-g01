<?php

declare(strict_types=1);

namespace ThePetPark;

use Doctrine\DBAL\Query\QueryBuilder;

interface FilterInterface
{
    public function apply(QueryBuilder $qb, string $field, $value);
}
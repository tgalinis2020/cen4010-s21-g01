<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Strategies\Sorting;

use ThePetPark\Library\Graph\Graph;
use ThePetPark\Library\Graph\ReferenceTable;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ThePetPark\Library\Graph\StrategyInterface;

/**
 * The simple sorting strategy sorts main data by the resource's attributes.
 */
class Simple implements StrategyInterface
{
    public function apply(
        Graph $graph,
        ReferenceTable $reftable,
        QueryBuilder $qb,
        CompositeExpression $conditions,
        array $params
    ): bool {

        $order = 'ASC';

        foreach ((explode(',', $params['sort'] ?? '')) as $attr) {

            if ($attr === '') {
                return false;
            }

            switch (substr($attr, 0, 1)) {
            case '-':
                $order = 'DESC';
            case '+':
                $attr = substr($attr, 1);
            }

            $ref = $reftable->getBaseRef();
            $resource = $graph->getByRef($ref);

            if ($resource->hasAttribute($attr) === false) {
                return false;
            }

            $qb->addOrderBy($attr, $order);

        }

        return true;

    }
}
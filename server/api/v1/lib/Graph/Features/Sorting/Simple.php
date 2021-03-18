<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Strategies\Sorting;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph;

/**
 * The simple sorting strategy sorts main data by the resource's attributes.
 */
class Simple implements Graph\FeatureInterface
{
    public function check(array $params): bool
    {
        return isset($params['sort']);
    }

    public function apply(Graph\App $graph, QueryBuilder $qb, array $params): bool
    {
        $order = 'ASC';

        foreach (explode(',', $params['sort']) as $attr) {
            if ($attr === '') {
                return false;
            }

            switch (substr($attr, 0, 1)) {
            case '-':
                $order = 'DESC';
            case '+':
                $attr = substr($attr, 1);
            }

            $ref = $graph->getBaseRef();
            $schema = $graph->getSchemaByRef($ref);

            if ($attr === 'id') {
                $attr = $schema->getId();
            } elseif ($schema->hasAttribute($attr) === false) {
                return false;
            }

            $qb->addOrderBy($ref . '.' . $attr, $order);
        }

        return true;
    }
}
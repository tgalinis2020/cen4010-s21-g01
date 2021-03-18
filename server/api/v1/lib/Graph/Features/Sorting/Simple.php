<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Features\Sorting;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph;

use function trim;

/**
 * The simple sorting strategy sorts main data by the resource's attributes.
 */
class Simple implements Graph\FeatureInterface
{
    public function check(array $params): bool
    {
        return isset($params['sort']);
    }

    public function clean(array &$params)
    {
        unset($params['sort']);
    }

    public function apply(Graph\App $graph, QueryBuilder $qb, array $params): bool
    {
        foreach (explode(',', $params['sort']) as $attr) {
            if ($attr === '') {
                return false;
            }

            $order = 'ASC';
            $attr = trim($attr);

            switch (substr($attr, 0, 1)) {
            case '-':
                $order = 'DESC';
            case '+':
                $attr = substr($attr, 1);
            }

            $ref = $graph->getBaseRef();
            $schema = $graph->getSchemaByRef($ref);

            if ($schema->hasAttribute($attr)) {
                $attr = $schema->getImplAttribute($attr);
            } else {
                return false;
            }

            $qb->addOrderBy($ref . '.' . $attr, $order);
        }

        return true;
    }
}
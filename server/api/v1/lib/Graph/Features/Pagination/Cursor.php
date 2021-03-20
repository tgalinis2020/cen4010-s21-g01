<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Features\Pagination;

use ThePetPark\Library\Graph\FeatureInterface;
use ThePetPark\Library\Graph;

/**
 * This pagination strategy is synonymous with applying a greater-than filter
 * on the provided ID in the cursor. Simple and efficient!
 */
class Cursor implements FeatureInterface
{
    public function check(array $params): bool
    {
        return isset($params['page'], $params['page']['cursor']);
    }

    public function clean(array &$params)
    {
        unset($params['page']);
    }

    public function apply(Graph\App $graph, array $params): bool
    {
        $qb = $graph->getQueryBuilder();
        $page = $params['page'];
        $size = $graph->getMaxPageSize();
        $ref = $graph->getBaseRef();
        $resource = $graph->getSchemaByRef($ref);

        $qb->andWhere($qb->expr()->gt(
            $ref . '.' . $resource->getId(),
            $qb->createNamedParameter($page['cursor'])
        ));

        if (isset($page['size']) && is_numeric($page['size'])) {
            $size = (int) $page['size'];
            unset($params['page']['size']);
        }

        $qb->setMaxResults($size);
        
        return true;
    }
}
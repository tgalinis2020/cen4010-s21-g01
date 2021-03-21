<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features\Pagination;

use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

/**
 * This pagination strategy applies a numerical offset to the returned records.
 * Not recommended for large amounts of data.
 */
class OffsetLimitStrategy implements Graph\FeatureInterface
{
    use Graph\Drivers\Doctrine\FeatureTrait;

    public function check(array $params): bool
    {
        return isset($params['page'], $params['page']['offset']);
    }

    public function apply(array $params, ReferenceTable $refs): bool
    {
        $page = $params['page'];
        $size = $this->driver->getDefaultPageSize();
        $qb = $this->getQueryBuilder();

        $qb->setFirstResult($page['offset']);

        if (isset($page['limit']) && is_numeric($page['limit'])) {
            $size = (int) $page['limit'];
        }

        $qb->setMaxResults($size);
        
        return true;
    }
}
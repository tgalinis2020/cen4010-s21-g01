<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features\Pagination;

use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

/**
 * Similar to the offset/limit strategy.
 * Offset is derived from page number and size of the page.
 * Again, not recommended for large sets of data.
 */
class PageStrategy implements Graph\FeatureInterface
{
    use Graph\Drivers\Doctrine\FeatureTrait;

    public function apply(array $params, ReferenceTable $refs): bool
    {
        if (isset($params['page'], $params['page']['number']) === false) {
            return false;
        }
    
        $size = $this->driver->getDefaultPageSize();
        $qb = $this->getQueryBuilder();
        $p = $params['page'];
        $page = ((int) $p['number']) - 1; // Pages start at 1

        if ($page < 1) {
            return false;
        }

        if (isset($p['size']) && is_numeric($p['size'])) {
            $size = (int) $p['size'];
        }

        $qb->setFirstResult($page * $size)->setMaxResults($size);

        return true;
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features\Pagination;

use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

use function is_numeric;

/**
 * This pagination strategy is synonymous with applying a greater-than filter
 * on the provided ID in the cursor. Simple and efficient!
 */
class CursorStrategy implements Graph\FeatureInterface
{
    use Graph\Drivers\Doctrine\FeatureTrait;

    public function check(array $params): bool
    {
        return isset($params['page'], $params['page']['cursor']);
    }

    public function apply(array $params, ReferenceTable $refs): bool
    {
        $page = $params['page'];
        $size = $this->driver->getDefaultPageSize();
        $ref = $refs->getBaseRef();
        $schema = $ref->getSchema();
        $qb = $this->getQueryBuilder();

        $qb->andWhere($qb->expr()->gt(
            $ref . '.' . $schema->getId(),
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
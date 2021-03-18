<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Strategies\Pagination;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use ThePetPark\Library\Graph\Graph;
use ThePetPark\Library\Graph\ReferenceTable;
use ThePetPark\Library\Graph\StrategyInterface;

/**
 * This pagination strategy is synonymous with applying a greater-than filter
 * on the provided ID in the cursor.
 */
class Cursor implements StrategyInterface
{
    public function apply(
        Graph $graph,
        QueryBuilder $qb,
        CompositeExpression $conditions,
        array $params
    ): bool {

        $reftable = $graph->getReferenceTable();
        $size = $graph->getMaxPageSize();

        if (isset($params['page'])) {
            $page = $params['page'];
            $ref = $reftable->getBaseRef();
            $resource = $graph->get($reftable->getResourceType($ref));

            if (isset($page['size']) && is_numeric($page['size'])) {
                $size = (int) $page['size'];
            }

            if (isset($page['cursor'])) {
                $conditions->add($qb->expr()->gt(
                    $ref . '.' . $resource->getId(),
                    $qb->createNamedParameter($page['cursor'])
                ));
            }

        }

        $qb->setMaxResults($size);
        
        return true;

    }
}
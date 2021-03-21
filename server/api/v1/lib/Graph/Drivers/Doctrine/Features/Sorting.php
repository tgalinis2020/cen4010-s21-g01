<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features;

use ThePetPark\Library\Graph;
use ThePetPark\Library\Graph\Drivers\Doctrine\Driver;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

use function trim;

/**
 * The simple sorting strategy sorts main data by the resource's attributes.
 */
class Sorting implements Graph\FeatureInterface
{
    use Graph\Drivers\Doctrine\FeatureTrait;

    public function check(array $params): bool
    {
        return isset($params['sort']);
    }

    public function apply(array $params, ReferenceTable $refs): bool
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

            $ref = $refs->getBaseRef();
            $schema = $ref->getSchema();

            if ($schema->hasAttribute($attr)) {
                $attr = $schema->getImplAttribute($attr);
            } else {
                return false;
            }

            $this->driver->getQueryBuilder()
                ->addOrderBy($ref . '.' . $attr, $order);
        }

        return true;
    }
}
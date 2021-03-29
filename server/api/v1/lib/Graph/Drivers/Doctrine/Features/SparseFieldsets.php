<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph\FeatureInterface;
use ThePetPark\Library\Graph\Schema;
use ThePetPark\Library\Graph\Schema\ReferenceTable;

use function trim;
use function substr;
use function explode;
use function array_pop;

/**
 * Sort main using the fields listed in the "sort" query parameter.
 * 
 * TODO:    The steps to resolving relationships is very similar to
 *          how its done when applying filters. Maybe it's possible to
 *          put shared logic into a driver method.
 */
class SparseFieldsets implements FeatureInterface
{
    /** @var \Doctrine\DBAL\Query\QueryBuilder*/
    protected $qb;

    /** @param \Doctrine\DBAL\Query\QueryBuilder */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    public function provides(): string
    {
        return 'fields';
    }

    public function apply(array $params, Schema\Container $schemas, ReferenceTable $refs): bool
    {
        foreach (($params['fields'] ?? []) as $type => $fieldset) {
            if ($schemas->has($type)) {
                $schema = $schemas->get($type);

                // Deselect all of the resource's attributes; only apply
                // those that are specified in the sparse fieldset. 
                $schema->clearFields();

                foreach (explode(',', $fieldset) as $attr) {
                    if ($schema->hasAttribute($attr)) {
                        $schema->addField($attr);
                    } else {
                        // Silently ignore invalid fields.
                    }
                }
            }
        }

        return true;
    }
}
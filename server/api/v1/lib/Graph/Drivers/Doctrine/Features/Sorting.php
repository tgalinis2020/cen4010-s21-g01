<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features;

use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph;
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
class Sorting implements Graph\FeatureInterface
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
        return 'sort';
    }

    public function apply(array $params, Schema\Container $schemas, ReferenceTable $refs): bool
    {
        foreach (explode(',', $params['sort']) as $fullyQualifiedField) {
            $ref = $refs->getBaseRef();
            $order = 'ASC';
            $fullyQualifiedField = trim($fullyQualifiedField);

            switch (substr($fullyQualifiedField, 0, 1)) {
            case '-':
                $order = 'DESC';
            case '+':
                $fullyQualifiedField = substr($fullyQualifiedField, 1);
            }

            $tokens = explode('.', $fullyQualifiedField);
            $field = array_pop($tokens);
            $delim = '';
            $token = '';

            foreach ($tokens as $relationship) {
                $token .= $delim . $relationship;

                $ref = $refs->has($token)
                   ? $refs->get($token)
                   : $refs->resolve($relationship, $ref);

                $delim = '.';
            }

            if ($field === 'id') {

                $field = $ref->getSchema()->getId();

            } elseif ($ref->getSchema()->hasAttribute($field)) {

                $field = $ref->getSchema()->getImplAttribute($field);

            } elseif ($ref->getSchema()->hasRelationship($field)) {

                $ref = $refs->has($fullyQualifiedField)
                   ? $refs->get($fullyQualifiedField)
                   : $refs->resolve($field, $ref);

                $field = $ref->getSchema()->getId();

            }

            $this->qb->addOrderBy($ref . '.' . $field, $order);
        }

        return true;
    }
}
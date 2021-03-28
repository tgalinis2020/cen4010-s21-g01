<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine\Features;

use ThePetPark\Library\Graph;
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
    use Graph\Drivers\Doctrine\FeatureTrait;

    public function apply(array $params, ReferenceTable $refs): bool
    {
        if (isset($params['sort']) === false) {
            return false;
        }

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

                // TODO:    Filters are evaulated before resolving any resources.
                //          Might not even be worth checking if a reference
                //          exists for the provided token.
                if ($refs->has($token)) {
                    $ref = $refs->get($token);
                } else {
                    $related = $refs->resolve($relationship, $ref);
                    $this->driver->resolve($related, $ref);
                    $ref = $related;
                }

                $ref = $related;
                $delim = '.';
            }

            if ($field === 'id') {

                $field = $ref->getSchema()->getId();

            } elseif ($ref->getSchema()->hasAttribute($field)) {

                $field = $ref->getSchema()->getImplAttribute($field);

            } elseif ($ref->getSchema()->hasRelationship($field)) {

                $token = $delim . $field;
                
                if ($refs->has($token)) {
                    $ref = $refs->get($token);
                } else {
                    $related = $refs->resolve($field, $ref);
                    $this->driver->resolve($related, $ref);
                    $ref = $related;
                }

                $field = $ref->getSchema()->getId();

            }

            $this->driver->getQueryBuilder()
                ->addOrderBy($ref . '.' . $field, $order);
        }

        return true;
    }
}
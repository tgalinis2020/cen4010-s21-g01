<?php

declare(strict_types=1);

namespace ThePetPark\Schema;

use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Container\ContainerInterface;
use ThePetPark\Schema;
use ThePetPark\Schema\Relationship as R;

use function substr;
use function strrchr;

/**
 * Wrapper for the Schema\Container that enumerates a resource and its
 * relationships, making them uniquely identifiable -- even if there are
 * relationships that resolve to the same type.
 */
class ReferenceTable implements ContainerInterface
{
    /**
     * Reference number of most recently enumerated value.
     * 
     * @var int
     */
    protected $refcount = 0;

    /**
     * Token-to-reference ID map.
     * 
     * @var array
     */
    protected $map = [];

    /**
     * Reference ID-to-reference map.
     * Root reference must be a Schema\Reference.
     * All other references are of type Schema\Relationship.
     * 
     * @var \ThePetPark\Schema\Relationship[]
     */
    protected $references = [];

    /**
     * Reference-to-parent reference map. Required for propagating relationship
     * data to the parent resource.
     * 
     * @var Schema\Reference[]
     */
    protected $parentRefs = [];

    /**
     * The source of data to select from.
     * 
     * Normally it is the root reference ID but it will change if requesting a
     * derived resource.
     * 
     * @var int
     */
    protected $baseRef;

    /** @var \ThePetPark\Schema\Container */
    protected $schemas;

    /** @var \Doctrine\DBAL\Query\QueryBuilder */
    protected $qb;

    /** @param \ThePetPark\Schema\Container $schemas */
    public function __construct(
        Schema\Container $schemas,
        string $base
    ) {
        $this->schemas = $schemas;
        $this->baseRef = $this->refcount;
        
        $ref = new Schema\Reference($this->baseRef, $schemas->get($base));
        
        $this->references[$this->refcount++] = $ref;
    }

    public function get($id): Schema\Relationship
    {
        return $this->references[$id];
    }

    public function has($id): bool
    {
        return isset($this->references[$id]);
    }

    /**
     * Generate a new unique reference for the relationship and add related
     * schema to the query.
     */
    public function resolve(
        string $token,
        Schema\Reference $source,
        QueryBuilder $qb
    ): Schema\Relationship {

        // If the relationship is already resolved, return it.
        if (isset($this->map[$token])) {
            return $this->references[$this->map[$token]];
        }

        $id = $this->refcount++;
        $name = substr(strrchr($token, '.') ?: '', 1) ?: $token;
        
        list($mask, $type, $link) = $source->getSchema()->getRelationship($name);

        $related = new Schema\Relationship(
            $id,
            $name,
            $link,
            $mask,
            $this->schemas->get($type)
        );

        $this->map[$token] = $id;
        $this->references[$id] = $related;

        if (is_array($link)) {
            $joinOn = (string) $source;
            $joinOnField = $source->getSchema()->getId();
            
            foreach ($link as $i => list($pivot, $from, $to)) {
                // Pivot tables need their own relation enums too.
                $pivotEnum = $source . '_' . $related . '_' . $i;
                
                $qb->innerJoin(
                    $joinOn,
                    $pivot,
                    $pivotEnum,
                    $qb->expr()->eq(
                        $joinOn    . '.' . $joinOnField,
                        $pivotEnum . '.' . $from
                    )
                );

                $joinOn = $pivotEnum;
                $joinOnField = $to;
            }

            // TODO: What if the chain doesn't end in the related resource's
            //       ID but in another relationship? Unlikely for this project
            //       but might want to consider other relationship fields in
            //       the future.
            $qb->innerJoin(
                $joinOn,
                $related->getSchema()->getImplType(),
                (string) $related,
                $qb->expr()->eq(
                    $joinOn  . '.' . $joinOnField,
                    $related . '.' . $related->getSchema()->getId()
                )
            );
        } else {
            $sourceField  = $source  . '.';
            $relatedField = $related . '.';

            if ($related->getType() & (R::MANY|R::OWNS)) {
                // foreign key is in related resource (resource owns another if
                // there exists a foreign key in the related resource)
                $sourceField  .= $source->getSchema()->getId();
                $relatedField .= $link;
            } else {
                // foreign key is in this resource (resource is owned by related)
                $sourceField  .= $link;
                $relatedField .= $related->getSchema()->getId();
            }

            $qb->innerJoin(
                (string) $source,
                $related->getSchema()->getImplType(),
                (string) $related,
                $qb->expr()->eq($sourceField, $relatedField)
            );
        }

        return $related;
    }

    /**
     * If selecting data derived from a resource's relationship, the
     * reference to the relationship should be promoted to the base reference.
     * 
     * Unset the relationship's row in parentRefs to avoid propagating
     * relationship data to a resource that isn't selected.
     */
    public function setBaseRef(Schema\Reference $ref, QueryBuilder $qb)
    {
        $this->baseRef = $ref->getRef();
        self::addSelect($qb, $ref);
    }

    public function setParentRef(Schema\Relationship $ref, Schema\Reference $parent, QueryBuilder $qb)
    {
        $this->parentRefs[$ref->getRef()] = $parent;
        self::addSelect($qb, $ref);
    }

    public function getBaseRef(): Schema\Reference
    {
        return $this->references[$this->baseRef];
    }

    /** @return \ThePetPark\Schema\Reference[] */
    public function getParentRefs(): array
    {
        return $this->parentRefs;
    }

    private static function addSelect(QueryBuilder $qb, Schema\Reference $source)
    {
        $schema = $source->getSchema();

        // Always select the resource's ID.
        $qb->addSelect(sprintf(
            '%1$s.%2$s %1$s_%3$s',
            $source,
            $schema->getId(),
            'id'
        ));

        // Add the attributes to the select statement, aliasing the fields
        // as {reference enum}_{attribute name}
        foreach ($schema->getImplAttributes() as list($attr, $impl)) {
            $qb->addSelect(sprintf(
                '%1$s.%2$s %1$s_%3$s',
                $source,
                $impl,
                $attr
            ));
        }
    }
}
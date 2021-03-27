<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use ThePetPark\Library\Graph\Schema;
use ThePetPark\Library\Graph\AbstractDriver;
use ThePetPark\Library\Graph\Schema\ReferenceTable;
use ThePetPark\Library\Graph\Schema\Relationship as R;

/**
 * TODO:    This driver is geared towards SQL-based solutions.
 *          Might be worth thinking about abstracting actions away from
 *          the Graph and delegate that to Drivers!
 *          Drivers should be the Action container.
 */
class Driver extends AbstractDriver
{
    /** @var \Doctrine\DBAL\Query\QueryBuilder */
    protected $qb;

    /** @var array */
    protected $settings;

    /**
     * @param \Doctrine\DBAL\Connection $conn
     * @param \ThePetPark\Library\Graph\FeatureInterface[] $features
     */
    public function __construct(Connection $conn, array $settings = [])
    {
        parent::__construct($settings['features']);
        $this->qb = $conn->createQueryBuilder();
        $this->settings = [
            'defaultPageSize' => $settings['defaultPageSize'] ?? 12,
        ];
    }

    /** @return \Doctrine\DBAL\Query\QueryBuilder */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->qb;
    }

    /**
     * @return int The default page size used in pagination strategies.
     */
    public function getDefaultPageSize(): int
    {
        return $this->settings['defaultPageSize'];
    }

    public function init(Schema\Reference $source)
    {
        $this->qb->select()->distinct()
            ->from($source->getSchema()->getImplType(), (string) $source);
    }

    public function apply(array $params, ReferenceTable $refs)
    {
        foreach ($this->features as $feat) {
            $feat->apply($params, $refs);
        }
    }

    public function reset(Schema\Reference $source)
    {
        $this->qb
            ->resetQueryParts(['select', 'distinct', 'orderBy'])
            ->setFirstResult(0)
            ->setMaxResults(null);

        // Always select the resource's ID.
        $this->qb->addSelect(sprintf(
            '%1$s.%2$s %1$s_%3$s',
            $source,
            $source->getSchema()->getId(),
            'id'
        ));
    }

    public function select(Schema\Reference $source, string $resourceID)
    {
        $this->qb->andWhere($this->qb->expr()->eq(
            $source . '.' . $source->getSchema()->getId(),
            $this->qb->createNamedParameter($resourceID)
        ));

        //$this->qb->setMaxResults(1);
    }

    public function prepare(Schema\Reference $source)
    {
        $schema = $source->getSchema();

        // Always select the resource's ID.
        $this->qb->addSelect(sprintf(
            '%1$s.%2$s %1$s_%3$s',
            $source,
            $schema->getId(),
            'id'
        ));

        // Add the attributes to the select statement, aliasing the fields
        // as {reference enum}_{attribute name}
        foreach ($schema->getAttributes() as list($attr, $impl)) {
            $this->qb->addSelect(sprintf(
                '%1$s.%2$s %1$s_%3$s',
                $source,
                $impl,
                $attr
            ));
        }
    }
    
    public function resolve(Schema\Reference $source, Schema\Relationship $related)
    {
        $link = $related->getLink();

        if (is_array($link)) {
            $joinOn = (string) $source;
            $joinOnField = $source->getSchema()->getId();
            
            foreach ($link as $i => list($pivot, $from, $to)) {
                // Pivot tables need their own relation enums too.
                $pivotEnum = $source . '_' . $related . '_' . $i;
                
                $this->qb->innerJoin($joinOn, $pivot, $pivotEnum, $this->qb->expr()->eq(
                    $joinOn    . '.' . $joinOnField,
                    $pivotEnum . '.' . $from
                ));

                $joinOn = $pivotEnum;
                $joinOnField = $to;
            }

            // TODO: What if the chain doesn't end in the related resource's
            //       ID but in another relationship? Unlikely for this project
            //       but might want to consider other relationship fields in
            //       the future.
            $this->qb->innerJoin(
                $joinOn,
                $related->getSchema()->getImplType(),
                (string) $related,
                $this->qb->expr()->eq(
                    $joinOn  . '.' . $joinOnField,
                    $related . '.'. $related->getSchema()->getId()
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

            $this->qb->innerJoin(
                (string) $source,
                $related->getSchema()->getImplType(),
                (string) $related,
                $this->qb->expr()->eq($sourceField, $relatedField)
            );
        }
    }

    public function setRange(Schema\Reference $source, string $from, string $to)
    {
        $idField = $source . '.' . $source->getSchema()->getId();

        // Only include data relevant to the previously fetched data.
        $this->qb->andWhere($this->qb->expr()->andX(
            $this->qb->expr()->gte($idField, $from),
            $this->qb->expr()->lte($idField, $to)
        ));
    }

    public function fetchAll(): array
    {
        return $this->qb->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function __toString(): string
    {
        return $this->qb->getSQL();
    }
}
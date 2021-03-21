<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Drivers\Doctrine;

use Doctrine\DBAL\Query\QueryBuilder;

trait FeatureTrait
{
    /** @var \ThePetPark\Library\Graph\Drivers\Doctrine\Driver */
    protected $driver;

    /** @param \Doctrine\DBAL\Query\QueryBuilder */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->driver->getQueryBuilder();
    }
}
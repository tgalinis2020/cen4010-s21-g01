<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Schema;

use ThePetPark\Library\Graph;

class Reference
{
    /** @var \ThePetPark\Library\Graph\Schema */
    protected $schema;

    /** @var int */
    protected $ref;

    public function __construct(int $id, Graph\Schema $schema)
    {
        $this->ref = $id;
        $this->schema = $schema;
    }

    public function getRef(): string
    {
        return 'r' . $this->ref;
    }

    public function getSchema(): Graph\Schema
    {
        return $this->schema;
    }

    public function __toString(): string
    {
        return $this->getRef();
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Schema;

use ThePetPark\Library\Graph\Schema;

class Reference
{
    /** @var string */
    protected $prefix;

    /** @var int */
    protected $ref;

    /** @var \ThePetPark\Library\Graph\Schema */
    protected $schema;

    public function __construct(int $id, Schema $schema, string $prefix = 'r')
    {
        $this->ref = $id;
        $this->schema = $schema;
        $this->prefix = $prefix;
    }

    public function getRef(): int
    {
        return $this->ref;
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function __toString(): string
    {
        return $this->prefix . $this->ref;
    }
}
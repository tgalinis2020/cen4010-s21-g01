<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

class Relationship
{
    // Relationship resolution bitfield values
    const ONE   = 1;
    const MANY  = 2;
    const OWNS  = 4;
    const OWNED = 8;

    /** @var \ThePetPark\Library\Graph\Schema */
    protected $related;

    /** @var int */
    protected $type;

    /** @var string */
    protected $ref;

    public function __construct(string $ref, Schema $schema, int $mask)
    {
        $this->ref = $ref;
        $this->related = $schema;
        $this->type = $mask;
    }

    public function getRef(): string
    {
        return $this->ref;
    }

    public function getSchema(): Schema
    {
        return $this->related;
    }

    public function getType(): int
    {
        return $this->type;
    }
}
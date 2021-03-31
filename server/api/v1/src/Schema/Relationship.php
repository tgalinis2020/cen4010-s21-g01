<?php

declare(strict_types=1);

namespace ThePetPark\Schema;

use ThePetPark\Schema;

class Relationship extends Reference
{
    // Relationship bitfield values
    const ONE   = 1;
    const MANY  = 2;
    const OWNS  = 4;
    const OWNED = 8;

    /** @var string */
    protected $name;

    /** @var int */
    protected $type;

    /** @var mixed */
    protected $link;

    public function __construct(int $id, string $name, $link, int $mask, Schema $schema)
    {
        parent::__construct($id, $schema);
        $this->name = $name;
        $this->type = $mask;
        $this->link = $link;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
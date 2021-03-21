<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph\Schema;

use Psr\Container\ContainerInterface;
use ThePetPark\Library\Graph;

class Container implements ContainerInterface
{
    /** @var \ThePetPark\Library\Graph\Schema */
    protected $schemas;

    /** @param \ThePetPark\Library\Graph\Schema[] $schemas */
    public function __construct(array $schemas = [])
    {
        $this->schemas = $schemas;
    }

    public function get(string $id): Graph\Schema
    {
        return $this->schemas[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->schemas[$id]);
    }
}
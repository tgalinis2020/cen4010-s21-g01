<?php

declare(strict_types=1);

namespace ThePetPark\Schema;

use Psr\Container\ContainerInterface;
use ThePetPark\Schema;

class Container implements ContainerInterface
{
    /** @var \ThePetPark\Schema[] */
    protected $schemas;

    /** @param \ThePetPark\Schema[] $schemas */
    public function __construct(array $schemas = [])
    {
        $this->schemas = $schemas;
    }

    public function get(string $type): Schema
    {
        return $this->schemas[$type];
    }

    public function has(string $type): bool
    {
        return isset($this->schemas[$type]);
    }
}
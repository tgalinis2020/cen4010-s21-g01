<?php

declare(strict_types=1);

namespace ThePetPark\Values;

use ThePetPark\DefaultValueInterface;

use date;

class CurrentDateTime implements DefaultValueInterface
{
    public function get(): string
    {
        return date('c');
    }
}
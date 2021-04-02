<?php

declare(strict_types=1);

namespace ThePetPark\Values;

use ThePetPark\DefaultValueInterface;

use function date;

class CurrentDateTime implements DefaultValueInterface
{
    public function get(): string
    {
        return date('c');
    }
}
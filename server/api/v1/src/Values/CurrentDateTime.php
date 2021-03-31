<?php

declare(strict_types=1);

namespace ThePetPark\Values;

use ThePetPark\DefaultValueInterface;

class CurrentDateTime implements DefaultValueInterface
{
    public function get()
    {
        return date('c');
    }
}
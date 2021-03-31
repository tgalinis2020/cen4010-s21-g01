<?php

declare(strict_types=1);

namespace ThePetPark\Values;

use ThePetPark\DefaultValueInterface;
use ThePetPark\Idp;

class ThePetParkIdpCode implements DefaultValueInterface
{
    public function get()
    {
        return Idp::THEPETPARK;
    }
}
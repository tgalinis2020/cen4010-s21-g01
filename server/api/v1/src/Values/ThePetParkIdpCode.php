<?php

declare(strict_types=1);

namespace ThePetPark\Values;

use ThePetPark\DefaultValueInterface;
use ThePetPark\Idp;

use function sprintf;

class ThePetParkIdpCode implements DefaultValueInterface
{
    public function get(): string
    {
        return sprintf("%d", Idp::THEPETPARK);
    }
}
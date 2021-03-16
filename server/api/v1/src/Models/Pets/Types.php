<?php

declare(strict_types=1);

namespace ThePetPark\Models\Pets;

use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph\Schema;

final class Types extends Schema
{
    protected function definitions()
    {
        $this->setType('pet-types');

        $this->addAttribute('type', 'pet_type');

        // Kind of odd that a tag can own a pet but "has" relationships are
        // defined such that the foreign key is in the related resource.
        $this->hasMany('pets', 'pets', 'type_id');
    }
}
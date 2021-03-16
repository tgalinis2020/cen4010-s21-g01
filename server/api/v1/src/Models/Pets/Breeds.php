<?php

declare(strict_types=1);

namespace ThePetPark\Models\Pets;

use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph\Schema;

final class Breeds extends Schema
{
    protected function definitions()
    {
        $this->setType('pet-breeds');

        $this->addAttribute('breed', 'pet_breed');

        // Kind of odd that a breed can own a pet but "has" relationships are
        // defined such that the foreign key is in the related resource.
        $this->hasMany('pets', 'pets', 'breed_id');
    }
}
<?php

declare(strict_types=1);

namespace ThePetPark\Models;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph\Schema;

final class Pets extends Schema
{
    protected function definitions()
    {
        $this->setType('pets');

        $this->addAttribute('name', 'pet_name');
        $this->addAttribute('description', 'pet_description');

        $this->belongsToOne('breed', 'pet-breeds', 'breed_id');
        $this->belongsToOne('type', 'pet-types', 'type_id');
        $this->belongsToOne('owner', 'users', 'user_id');
    }
}
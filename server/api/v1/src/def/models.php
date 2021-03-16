<?php

declare(strict_type=1);

use ThePetPark\Models;

return [
    Models\Users::class,
    Models\Pets::class,
    Models\Pets\Breeds::class,
    Models\Pets\Types::class,
    Models\Posts::class,
    Models\Tags::class,
    Models\Comments::class,
];
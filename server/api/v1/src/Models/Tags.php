<?php

declare(strict_types=1);

namespace ThePetPark\Models;

use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph\Schema;

final class Tags extends Schema
{
    protected function definitions()
    {
        $this->setType('tags');

        $this->addAttribute('text', 'tag_text');

        $this->belongsToMany('posts', 'posts', [
            ['post_tags', 'tag_id', 'post_id'],
        ]);
    }
}
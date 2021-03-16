<?php

declare(strict_types=1);

namespace ThePetPark\Models;

use Doctrine\DBAL\Connection;
use ThePetPark\Library\Graph\Schema;

final class Comments extends Schema
{
    protected function definitions()
    {
        $this->setType('comments');

        $this->addAttribute('text', 'text_content');
        $this->addAttribute('createdAt', 'created_at');

        $this->belongsToOne('author', 'users', 'user_id');
        $this->belongsToMany('posts', 'posts', [
            ['post_comments', 'comment_id', 'post_id'],
        ]);
    }
}
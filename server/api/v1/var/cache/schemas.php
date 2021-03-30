<?php return array (
  0 => 
  array (
    0 => 
    array (
      0 => 'posts',
      1 => 'posts',
      2 => 'id',
    ),
    1 => 
    array (
      'title' => 
      array (
        0 => 'title',
        1 => 'title',
      ),
      'text' => 
      array (
        0 => 'text',
        1 => 'text_content',
      ),
      'image' => 
      array (
        0 => 'image',
        1 => 'image_url',
      ),
      'createdAt' => 
      array (
        0 => 'createdAt',
        1 => 'created_at',
      ),
    ),
    2 => 
    array (
      'author' => 
      array (
        0 => 9,
        1 => 'users',
        2 => 'user_id',
      ),
      'likes' => 
      array (
        0 => 10,
        1 => 'users',
        2 => 
        array (
          0 => 
          array (
            0 => 'post_likes',
            1 => 'post_id',
            2 => 'user_id',
          ),
        ),
      ),
      'tags' => 
      array (
        0 => 6,
        1 => 'tags',
        2 => 
        array (
          0 => 
          array (
            0 => 'post_tags',
            1 => 'post_id',
            2 => 'tag_id',
          ),
        ),
      ),
      'pets' => 
      array (
        0 => 6,
        1 => 'pets',
        2 => 
        array (
          0 => 
          array (
            0 => 'post_pets',
            1 => 'post_id',
            2 => 'pet_id',
          ),
        ),
      ),
      'comments' => 
      array (
        0 => 6,
        1 => 'comments',
        2 => 
        array (
          0 => 
          array (
            0 => 'post_comments',
            1 => 'post_id',
            2 => 'comment_id',
          ),
        ),
      ),
    ),
    3 => 
    array (
    ),
  ),
  1 => 
  array (
    0 => 
    array (
      0 => 'comments',
      1 => 'comments',
      2 => 'id',
    ),
    1 => 
    array (
      'text' => 
      array (
        0 => 'text',
        1 => 'text_content',
      ),
      'createdAt' => 
      array (
        0 => 'createdAt',
        1 => 'created_at',
      ),
    ),
    2 => 
    array (
      'author' => 
      array (
        0 => 9,
        1 => 'users',
        2 => 'user_id',
      ),
      'post' => 
      array (
        0 => 9,
        1 => 'posts',
        2 => 
        array (
          0 => 
          array (
            0 => 'post_comments',
            1 => 'comment_id',
            2 => 'post_id',
          ),
        ),
      ),
    ),
    3 => 
    array (
    ),
  ),
  2 => 
  array (
    0 => 
    array (
      0 => 'users',
      1 => 'users',
      2 => 'id',
    ),
    1 => 
    array (
      'email' => 
      array (
        0 => 'email',
        1 => 'email',
      ),
      'username' => 
      array (
        0 => 'username',
        1 => 'username',
      ),
      'firstName' => 
      array (
        0 => 'firstName',
        1 => 'first_name',
      ),
      'lastName' => 
      array (
        0 => 'lastName',
        1 => 'last_name',
      ),
      'avatar' => 
      array (
        0 => 'avatar',
        1 => 'avatar_url',
      ),
      'idpCode' => 
      array (
        0 => 'idpCode',
        1 => 'idp_code',
      ),
      'createdAt' => 
      array (
        0 => 'createdAt',
        1 => 'created_at',
      ),
    ),
    2 => 
    array (
      'pets' => 
      array (
        0 => 6,
        1 => 'pets',
        2 => 'user_id',
      ),
      'posts' => 
      array (
        0 => 6,
        1 => 'posts',
        2 => 'user_id',
      ),
      'comments' => 
      array (
        0 => 6,
        1 => 'comments',
        2 => 'user_id',
      ),
      'favorites' => 
      array (
        0 => 6,
        1 => 'posts',
        2 => 
        array (
          0 => 
          array (
            0 => 'user_favorite_posts',
            1 => 'user_id',
            2 => 'post_id',
          ),
        ),
      ),
      'subscriptions' => 
      array (
        0 => 6,
        1 => 'pets',
        2 => 
        array (
          0 => 
          array (
            0 => 'subscriptions',
            1 => 'user_id',
            2 => 'pet_id',
          ),
        ),
      ),
    ),
    3 => 
    array (
    ),
  ),
  3 => 
  array (
    0 => 
    array (
      0 => 'tags',
      1 => 'tags',
      2 => 'id',
    ),
    1 => 
    array (
      'text' => 
      array (
        0 => 'text',
        1 => 'text_content',
      ),
    ),
    2 => 
    array (
      'posts' => 
      array (
        0 => 10,
        1 => 'posts',
        2 => 
        array (
          0 => 
          array (
            0 => 'post_tags',
            1 => 'tag_id',
            2 => 'post_id',
          ),
        ),
      ),
    ),
    3 => 
    array (
    ),
  ),
  4 => 
  array (
    0 => 
    array (
      0 => 'pets',
      1 => 'pets',
      2 => 'id',
    ),
    1 => 
    array (
      'name' => 
      array (
        0 => 'name',
        1 => 'pet_name',
      ),
      'description' => 
      array (
        0 => 'description',
        1 => 'pet_description',
      ),
      'avatar' => 
      array (
        0 => 'avatar',
        1 => 'avatar_url',
      ),
    ),
    2 => 
    array (
      'owner' => 
      array (
        0 => 9,
        1 => 'users',
        2 => 'user_id',
      ),
      'posts' => 
      array (
        0 => 10,
        1 => 'posts',
        2 => 
        array (
          0 => 
          array (
            0 => 'post_pets',
            1 => 'pet_id',
            2 => 'post_id',
          ),
        ),
      ),
      'breed' => 
      array (
        0 => 9,
        1 => 'pet-breeds',
        2 => 'breed_id',
      ),
      'type' => 
      array (
        0 => 9,
        1 => 'pet-types',
        2 => 'type_id',
      ),
    ),
    3 => 
    array (
    ),
  ),
  5 => 
  array (
    0 => 
    array (
      0 => 'pet-breeds',
      1 => 'pet_breeds',
      2 => 'id',
    ),
    1 => 
    array (
      'breed' => 
      array (
        0 => 'breed',
        1 => 'pet_breed',
      ),
    ),
    2 => 
    array (
      'pets' => 
      array (
        0 => 6,
        1 => 'pets',
        2 => 'breed_id',
      ),
    ),
    3 => 
    array (
    ),
  ),
  6 => 
  array (
    0 => 
    array (
      0 => 'pet-types',
      1 => 'pet_types',
      2 => 'id',
    ),
    1 => 
    array (
      'type' => 
      array (
        0 => 'type',
        1 => 'pet_type',
      ),
    ),
    2 => 
    array (
      'pets' => 
      array (
        0 => 6,
        1 => 'pets',
        2 => 'type_id',
      ),
    ),
    3 => 
    array (
    ),
  ),
);
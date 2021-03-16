<?php return array (
  0 => 
  array (
    0 => 'ThePetPark\\Library\\Graph\\Handlers\\NotImplemented',
    1 => 'ThePetPark\\Library\\Graph\\Handlers\\JSONAPI\\Resolver',
    2 => 'ThePetPark\\Http\\Actions\\Posts\\Create',
    3 => 'ThePetPark\\Http\\Actions\\Posts\\Update',
    4 => 'ThePetPark\\Http\\Actions\\Posts\\Delete',
  ),
  1 => 
  array (
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
        'text' => 
        array (
          0 => 0,
          1 => 'text',
          2 => 'text_content',
        ),
        'image' => 
        array (
          0 => 0,
          1 => 'image',
          2 => 'image_url',
        ),
        'likes' => 
        array (
          0 => 0,
          1 => 'likes',
          2 => 'like_count',
        ),
        'createdAt' => 
        array (
          0 => 0,
          1 => 'createdAt',
          2 => 'created_at',
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
      ),
      3 => 
      array (
        0 => 
        array (
          'GET' => 1,
          'POST' => 2,
          'PUT' => 0,
          'PATCH' => 3,
          'DELETE' => 4,
        ),
        1 => 
        array (
          'GET' => 0,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
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
          0 => 0,
          1 => 'text',
          2 => 'text_content',
        ),
        'createdAt' => 
        array (
          0 => 0,
          1 => 'createdAt',
          2 => 'created_at',
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
        'posts' => 
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
        0 => 
        array (
          'GET' => 1,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
        1 => 
        array (
          'GET' => 0,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
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
          0 => 0,
          1 => 'email',
          2 => 'email',
        ),
        'username' => 
        array (
          0 => 0,
          1 => 'username',
          2 => 'username',
        ),
        'firstName' => 
        array (
          0 => 0,
          1 => 'firstName',
          2 => 'first_name',
        ),
        'lastName' => 
        array (
          0 => 0,
          1 => 'lastName',
          2 => 'last_name',
        ),
        'avatar' => 
        array (
          0 => 0,
          1 => 'avatar',
          2 => 'avatar_url',
        ),
        'idpCode' => 
        array (
          0 => 0,
          1 => 'idpCode',
          2 => 'idp_code',
        ),
        'createdAt' => 
        array (
          0 => 0,
          1 => 'createdAt',
          2 => 'created_at',
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
        0 => 
        array (
          'GET' => 1,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
        1 => 
        array (
          'GET' => 0,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
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
          0 => 0,
          1 => 'text',
          2 => 'text_content',
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
        0 => 
        array (
          'GET' => 1,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
        1 => 
        array (
          'GET' => 0,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
      ),
    ),
    4 => 
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
          0 => 0,
          1 => 'breed',
          2 => 'pet_breed',
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
        0 => 
        array (
          'GET' => 1,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
        1 => 
        array (
          'GET' => 0,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
      ),
    ),
    5 => 
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
          0 => 0,
          1 => 'type',
          2 => 'pet_type',
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
        0 => 
        array (
          'GET' => 1,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
        1 => 
        array (
          'GET' => 0,
          'POST' => 0,
          'PUT' => 0,
          'PATCH' => 0,
          'DELETE' => 0,
        ),
      ),
    ),
  ),
);
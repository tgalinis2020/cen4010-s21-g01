DROP DATABASE IF EXISTS cen4010_s21_g01;
CREATE DATABASE IF NOT EXISTS cen4010_s21_g01;

USE cen4010_s21_g01;

CREATE TABLE users (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(255),
    created_at DATETIME NOT NULL,

    -- This value corresponds to an IDP code that the application supports.
    -- The application itself is an IDP (code = 0).
    idp_code INTEGER UNSIGNED NOT NULL DEFAULT 0,
    
    CONSTRAINT users_pk PRIMARY KEY (id),
    CONSTRAINT users_username_uk UNIQUE (username),
    CONSTRAINT users_email_uk UNIQUE (email)
);

-- If additional identity providers are supported, it isn't necessary to store
-- passwords for users that registered using them. Therefore the password field
-- has been abstracted into its own relation: it only applies to users whose IDP
-- is the app itself (idp_id = 0).
CREATE TABLE user_passwords (
    id INTEGER UNSIGNED NOT NULL,

    -- PHP uses the bcrypt hashing algorithm for its password_hash function,
    -- the result will always be a fixed-size 60 character string.
    -- The PHP docs recommend to store the password in a variable length column.
    passwd VARCHAR(255) NOT NULL,

    CONSTRAINT passwd_pk PRIMARY KEY (id),
    CONSTRAINT passwd_users_fk FOREIGN KEY (id) REFERENCES users (id)
);

-- Users specify a pet type and breed when they create a pet.
-- To avoid unecessary duplication, both of these attributes have been
-- abstracted away into their own relations.
CREATE TABLE pet_breeds (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    pet_breed VARCHAR (255) NOT NULL,

    CONSTRAINT pet_breeds_pk PRIMARY KEY (id),
    CONSTRAINT pet_breeds_uk UNIQUE (pet_breed)
);

CREATE TABLE pet_types (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    pet_type VARCHAR(255) NOT NULL,

    CONSTRAINT pet_types_pk PRIMARY KEY (id),
    CONSTRAINT pet_types_uk UNIQUE (pet_type)
);

CREATE TABLE pets (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    pet_name VARCHAR(255) NOT NULL,
    pet_description TEXT,
    avatar_url VARCHAR(255),
    type_id  INTEGER UNSIGNED NOT NULL,
    breed_id INTEGER UNSIGNED,
    user_id INTEGER UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,

    CONSTRAINT pets_pk PRIMARY KEY (id),
    CONSTRAINT pets_type_fk FOREIGN KEY (type_id) REFERENCES pet_types (id),
    CONSTRAINT pets_breed_fk FOREIGN KEY (breed_id) REFERENCES pet_breeds (id),  
    CONSTRAINT pets_users_fk FOREIGN KEY (user_id) REFERENCES users (id)
);

-- User's home feed will be populated by posts from the pets they follow.
-- Many users -> Many pets
CREATE TABLE subscriptions (
    pet_id INTEGER UNSIGNED NOT NULL,
    user_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT subscriptions_pk PRIMARY KEY (pet_id, user_id),
    CONSTRAINT subscriptions_pet_fk FOREIGN KEY (pet_id) REFERENCES pets (id),
    CONSTRAINT subscriptions_user_fk FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE TABLE posts (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    image_url VARCHAR(512) NOT NULL,
    title VARCHAR(255) NOT NULL,
    text_content TEXT,
    created_at DATETIME NOT NULL,
    user_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT posts_pk PRIMARY KEY (id),
    CONSTRAINT author_fk FOREIGN KEY (user_id) REFERENCES users (id)
);

-- One post -> Many likes
CREATE TABLE post_likes (
    post_id INTEGER UNSIGNED NOT NULL,
    user_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT post_likes_pk PRIMARY KEY (post_id, user_id),
    CONSTRAINT post_likes_posts_fk FOREIGN KEY (post_id) REFERENCES posts (id),
    CONSTRAINT post_likes_users_fk FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE TABLE comments (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    text_content TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    user_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT comments_pk PRIMARY KEY (id),
    CONSTRAINT comments_author_fk FOREIGN KEY (user_id) REFERENCES users (id)
);

-- One post -> Many comments
-- It's possible to make this a to-one foreign key directly in the comments
-- table, but putting the association here makes it possible to associate
-- comments with other comments in the future (if there is time :)).
CREATE TABLE post_comments (
    comment_id INTEGER UNSIGNED NOT NULL,
    post_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT post_comments_pk PRIMARY KEY (comment_id),
    CONSTRAINT post_comments_comment_fk FOREIGN KEY (comment_id) REFERENCES comments (id),
    CONSTRAINT post_comments_posts_fk FOREIGN KEY (post_id) REFERENCES posts (id)
);

-- Tags can be used to search for posts.
CREATE TABLE tags (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    text_content VARCHAR(64) NOT NULL,

    CONSTRAINT tags_pk PRIMARY KEY (id),
    CONSTRAINT tags_uk UNIQUE (text_content)
);

-- Many post -> many tags
CREATE TABLE post_tags (
    post_id INTEGER UNSIGNED NOT NULL,
    tag_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT post_tags_pk PRIMARY KEY (post_id, tag_id),
    CONSTRAINT post_tags_post_fk FOREIGN KEY (post_id) REFERENCES posts (id),
    CONSTRAINT post_tags_tag_fk FOREIGN KEY (tag_id) REFERENCES tags (id)
);

-- Many posts -> Many pets
CREATE TABLE post_pets (
    post_id INTEGER UNSIGNED NOT NULL,
    pet_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT post_pets_pk PRIMARY KEY (post_id, pet_id),
    CONSTRAINT post_pets_post_fk FOREIGN KEY (post_id) REFERENCES posts (id),
    CONSTRAINT post_pets_pet_fk FOREIGN KEY (pet_id) REFERENCES pets (id)
);

-- Many users -> Many posts
CREATE TABLE user_favorite_posts (
    user_id INTEGER UNSIGNED NOT NULL,
    post_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT user_favorite_posts_pk PRIMARY KEY (user_id, post_id),
    CONSTRAINT favoirte_user_fk FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT favoirte_post_fk FOREIGN KEY (post_id) REFERENCES posts (id)
);

-- Some dummy values used for testing
INSERT INTO users (email, username, first_name, last_name, created_at)
VALUES      ('john_doe@example.com', 'jdoe123', 'John', 'Doe', NOW()),
            ('jane_smith@example.com', 'jsmith456', 'Jane', 'Smith', NOW()),
            ('bob@example.com', 'bob0', 'Bob', 'Bob', NOW());

INSERT INTO posts (image_url, user_id, created_at, title, text_content)
VALUES      ('/uploads/img0.png', 1, NOW(), 'Cat!', 'Look at this cute cat!'),
            ('/uploads/img1.png', 2, NOW(), 'Birb!', 'Birb.');

INSERT INTO tags (text_content)
VALUES      ('birb'),
            ('cute'),
            ('cat');

INSERT INTO post_tags (post_id, tag_id)
VALUES      (1, 2),
            (1, 3),
            (2, 1),
            (2, 2);

INSERT INTO comments (user_id, created_at, text_content)
VALUES      (2, NOW(), 'That is a very cute cat. Thank you for sharing!'),
            (3, NOW(), 'Lookit da kitty!!!'),
            (1, NOW(), 'Borb.');

INSERT INTO post_comments (comment_id, post_id)
VALUES      (1, 1),
            (2, 1),
            (3, 2);
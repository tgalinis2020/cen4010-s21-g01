CREATE DATABASE thepetpark;

USE thepetpark;

CREATE TABLE users (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,

    -- This value corresponds to an IDP code that the application supports.
    -- The application itself is an IDP (code = 0).
    idp_id INTEGER UNSIGNED NOT NULL DEFAULT 0,
    
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
    CONSTRAINT passwd_users_fk FOREIGN KEY (id) REFERENCES users (id),
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
    type_id  INTEGER UNSIGNED NOT NULL,
    breed_id INTEGER UNSIGNED,
    user_id INTEGER UNSIGNED NOT NULL,
    avatar_url VARCHAR(255) NOT NULL,

    CONSTRAINT pets_pk PRIMARY KEY (id),
    CONSTRAINT pets_type_fk FOREIGN KEY (type_id) REFERENCES pet_types (id),
    CONSTRAINT pets_breed_fk FOREIGN KEY (breed_id) REFERENCES pet_breeds (id),  
    CONSTRAINT pets_users_fk FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE TABLE user_favorite_posts (
    user_id INTEGER UNSIGNED NOT NULL,
    post_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT user_favorite_posts_pk PRIMARY KEY (user_id, post_id),
    CONSTRAINT favoirte_user_fk FOREIGN KEY (user_id) REFERENCES user (id),
    CONSTRAINT favoirte_post_fk FOREIGN KEY (post_id) REFERENCES posts (id)
);

-- User's home feed will be populated by posts from the pets they follow.
CREATE TABLE subscriptions (
    pet_id INTEGER UNSIGNED NOT NULL,
    user_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT subscriptions_pk PRIMARY KEY (pet_id, user_id),
    CONSTRAINT subscriptions_pet_fk FOREIGN KEY (pet_id) REFERENCES pets (id),
    CONSTRAINT subscriptions_user_fk FOREIGN KEY (user_id) REFERENCES users (id)
);

-- Even though expansion of the app is unlikely after this class is over,
-- it may be worth abstracting the likes from posts in case other things can
-- be liked in the future.
CREATE TABLE likeables (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    like_count INTEGER UNSIGNED NOT NULL DEFAULT 0,

    CONSTRAINT likeable_pk PRIMARY KEY (id)
);

CREATE TABLE posts (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    image_url VARCHAR(512) NOT NULL,
    text_content TEXT,
    likeable_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT posts_pk PRIMARY KEY (id),
    CONSTRAINT posts_likeables_fk FOREIGN KEY (likeable_id) REFERENCES likeables (id)
);

-- Tags can be used to search for posts.
CREATE TABLE tags (
    id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    tag_text VARCHAR(64) NOT NULL,

    CONSTRAINT tags_pk PRIMARY KEY (id),
    CONSTRAINT tags_uk UNIQUE (tag_text)
);

CREATE TABLE post_tags (
    post_id INTEGER UNSIGNED NOT NULL,
    tag_id INTEGER UNSIGNED NOT NULL,

    CONSTRAINT post_tags_pk PRIMARY KEY (post_id, tag_id),
    CONSTRAINT post_tags_post_fk FOREIGN KEY (post_id) REFERENCES posts (id),
    CONSTRAINT post_tags_tag_fk FOREIGN KEY (tag_id) REFERENCES tags (id)
);
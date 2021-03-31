# Resource Graph: A library for managing API resources

Author: Thomas Galinis

At first, The Pet Park's API resource routes were mapped by hand.
After realizing how many routes to resources and their corresponding relationships
had to be made, I was thinking that the methods of fetching data were the same.
In an attempt to minimize copy-paste and automate the selection of API resources,
this library came to. Decided to follow the [JSON:API](https://jsonapi.org/format/)
specification to allow for expressive and efficient queries. Effort has been
made to make this library reusable, but the project has a hard dependency
on Doctrine's Database Abstraction Layer library.


Similar and more robust tools most likely already exist, but this was an
academic endeavour. Been itching to solve this problem for a while now.
There are a couple of limitations, however:

* Resource attributes must be part of the source table.

* Consequently, can't derive attributes from other tables or functions.
  For instance, can't have an attribute called "tags" for the "posts" resource,
  but it can be included from a relationship if tags are a resource.
  Also can't have an attribute called "likes" be equal to `COUNT(*)` from some relation.

* Attributes can only be one-dimensional.

* Attributes can only be strings, even if their types are numeric.

* Currently there's no way to bind default values to attributes when creating
  a resource.

* Probably the biggest problem: there is no form of access control whatsoever.
  This library has one purpose: map backend actions to URLs following the
  JSON:API specification. There's nothing to stop mischievous actors to
  edit, say, a post's author by making a PATCH request to
  `/posts/1/relationships/author`.


## Dependecies

- `ext_yaml:^2.2.1` So far the only supported format for Graph definitions is
  YAML. It's convenient and doesn't require as many keystrokes as XML.
  Might consider supporting JSON in the future. In the end, the definitions
  are serialized into a structured PHP array.

- `doctrine/dbal:^2.5` The main driver for this library is implemented using
  Doctrine's DBAL QueryBuilder component.


## How-To

The idea behind this library is to declaratively define a back-end's schema.
Using these definitions, a SQL query can be generated based on a URL.
For starters, you'll need to create a definitions file.

```yaml
# Resource mappings are defined here. This is not a complete example.
schemas:
    posts:
        # If the resource's corresponding table name doesn't match the resource's
        # type, specify the table's name here.
        src: user_posts

        # ID field mappings default to "id"; if your table's primary key
        # has a different field name, specify it here.
        id: post_id

        attributes:
            # Attribute is defined before the colon. Its implementation name
            # follows after.
            text: post_content

            # If the attribute and implementation names are the same, you can
            # specify this using a dollar-sign.
            likes: $

        # Note: might be obvious, but any resource that a relationship resolves
        # to must have its schema defined in this file.
        relationships:
            author:
                # Relationships be any of the following types:
                # belongsTo, belongsToMany, has, and hasMany
                # The type of resource the relationship resolves to is specified
                # to the right of the colon.
                belongsTo: users

                # The ownership property of the relationship determines where
                # the foreign key lives. In "belongsTo" relationships, the key
                # exists in this resource's table.
                using: user_id

            tags:
                hasMany: tags
                using:
                    - relation: post_tags
                      from: post_id
                      to: tag_id

            foos:
                hasMany: foos

                # For more complex relationships, a relationship chain can
                # be specfied instead of a single foreign key.
                # The first item in the chain uses this resource's primary key
                # to join with the relation. In the last item in the chain,
                # the "to" field must correspond to the foreign key that
                # ultimately resolves to the target resource.
                using:
                    - relation: posts_bars
                      from: post_id
                      to: bar_id

                    - relation: bar_foos
                      from: bar_id
                      to: foo_id
```

For a complete example, see The Pet Park's graph in `/server/api/v1/etc/graph.yml`.

Once the schema has been created, it must be compiled into a format that the
library can read. This can be done using the `bin/graph` console utility.

```console
$ pwd
/path/to/server/api/v1

$ php bin/graph
usage: php bin/graph <yml-src> <cache-dest>

```

The utility takes a YAML definitions file as the first argument and the
destination for the compiled definitions as the second argument.

```console
$ php bin/graph etc/my-graph.yml var/cache/my-graph.cache.php
```
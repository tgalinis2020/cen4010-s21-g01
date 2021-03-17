# Graph: A library for managing API resources

At first, the API's resource routes were mapped by hand.
After realizing how many routes to resources and their corresponding relationships
had to be made, I was thinking that the methods of fetching data were the same.
In an attempt to minimize copy-paste and automate the selection of API resources,
this library was born. Decided to partially follow the [JSON:API](https://jsonapi.org/format/)
specification to allow for expressive and efficient queries.


Tools that do similar things probably exist, but I don't care.
This is an acedemic endeavor, anyways. It was fun :) (and laziness -- who has time
to define 10+ routes that perform a similar task?!)


Might change the name of the library later since GraphQL is a thing.
Wasn't feeling very creative initially.


## Dependecies

- `ext_yaml:^2.2.1` So far the only supported format for Graph definitions is
  YAML. It's convenient and doesn't require as many keystrokes as XML.
  Might consider supporting JSON in the future. In the end, the definitions
  are serialized into a structured PHP array.

- `doctrine/bal:^2.5` This library makes heavy use of Doctrine's QueryBuilder
  to dynamiclly generate queries based on the URL and Graph definitions.


## How-To

The idea behind this library is to declaratively define a back-end's schema.
Using these definitions, a SQL query can be generated based on a URL.
For starters, you'll need to create a Graph definitions file.

```yaml
# Default actions map to schemas that haven't implemented an action to handle
# a request type.
actions:
    # For regular use-cases, use resource actions.
    resources:
        GET: Path\To\Graph\Actions\ResourceResolver
    
    # Relationship actions -- relevant for relationship queries.
    # i.e. the "relationship" keyword is present between a resource's ID
    # and relationship.
    relationships:
        GET: Path\To\My\RelationshipResolver

# Resource mappings are defined here. This is not a complete example.
schemas:
    posts:
        # If the resource's corresponding table name doesn't match the resource's
        # type, specify the table's name here.
        src: user_posts

        # ID field mappings default to "id"; if your table's primary key
        # has a different field name, specify it here.
        id: post_id

        actions:
            # As stated before, default actions will be mapped to unimplemented
            # schema actions. If there is no action to handle an event, Graph
            # will return a 501 Not Implemented back to the client.
            resource:
                POST: Path\To\My\Posts\Create

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

For a complete example, see ThePetPark's graph in `/server/api/v1/etc/graph.yml`.

Once the schema has been created, it must be compiled into a format that the
library can read. This can be done using the `bin/graph` console utility.

```console
$ pwd
/path/to/server/api/v1

$ php bin/graph
usage: php bin/graph <yml-src> <cache-dest>

```

The utility takes a YAML definitions file as the first argument and the
destination to the compiled definitions as the second argument.

```console
$ php bin/graph etc/my-graph.yml var/cache/my-graph.cache
```

When providing settings for a Graph instance in PHP, provide the absolute
path to the compiled file in the "definitions" settings key.
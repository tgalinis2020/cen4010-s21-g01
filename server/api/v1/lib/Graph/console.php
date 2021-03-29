<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use ThePetPark\Library\Graph\App as Graph;
use ThePetPark\Library\Graph\Actions;
use ThePetPark\Library\Graph\Schema\Relationship as R;

if ($argc < 3) {
    printf('usage: php %s <yml-src> <cache-dest>', $argv[0]);
    echo "\n";
    exit(1);
}

list($self, $src, $dest) = $argv;

/** @var array */
$root = yaml_parse_file($src);

/** @var array */
$cache = [];

/** @var array */
$schemas = [];
$typeSchemaMap = [];
$nschemas = 0;

/** @var \ThePetPark\Library\Graph\ActionInterface[] */
$actions = [
    Actions\NotImplemented::class,
];

/** @var int */
$nactions = 1;

/** @var array */
$defaultActions = [];

/** @var \ThePetPark\Library\Graph\ActionInterface[] */
$defaultActions[Graph::RESOURCE_CONTEXT]     = [];

/** @var \ThePetPark\Library\Graph\ActionInterface[] */
$defaultActions[Graph::RELATIONSHIP_CONTEXT] = [];

$contextTypes = [
    'resource'      => Graph::RESOURCE_CONTEXT,
    'relationship'  => Graph::RELATIONSHIP_CONTEXT,
];

$relationshipTypes = [
    'belongsTo'     => R::OWNED|R::ONE,
    'belongsToMany' => R::OWNED|R::MANY,
    'has'           => R::OWNS|R::ONE,
    'hasMany'       => R::OWNS|R::MANY,
];

try {

    // Get default schema actions
    if (isset($root['actions'])) {
        foreach ($contextTypes as $ctx => $type) {
            foreach ($root['actions'][$ctx] ?? [] as $httpVerb => $action) {
                $defaultActions[$type][$httpVerb] = $nactions++;
                $actions[] = $action; // register default action
            }
        }
    }

    if (isset($root['schemas'])) {
        foreach ($root['schemas'] as $resource => $def) {
            $id            = $def['id'] ?? 'id';
            $source        = $def['src'] ?? $resource;
            $actionMap     = [];
            $attributes    = [];
            $relationships = [];

            // Initialize action map using not-implemented action.
            foreach ($contextTypes as $type) {
                $actionMap[$type] = [
                    'GET'     => Graph::ACTION_NOT_IMPL,
                    'POST'    => Graph::ACTION_NOT_IMPL,
                    'PUT'     => Graph::ACTION_NOT_IMPL,
                    'PATCH'   => Graph::ACTION_NOT_IMPL,
                    'DELETE'  => Graph::ACTION_NOT_IMPL,
                ];
            }

            foreach ($contextTypes as $ctx => $type) {
                foreach ($def['actions'][$ctx] ?? [] as $httpVerb => $action) {
                    $actionMap[$type][$httpVerb] = $nactions++;
                    $actions[] = $action; // register schema action
                }

                // Map defaults to unimplemented schema actions.
                foreach ($defaultActions[$type] as $httpVerb => $actionEnum) {
                    if ($actionMap[$type][$httpVerb] === Graph::ACTION_NOT_IMPL) {
                        $actionMap[$type][$httpVerb] = $actionEnum;
                    }
                }
            }

            foreach ($def['attributes'] ?? [] as $attr => $field) {
                // 0 = selectable value (default is 0 if no sparse fields specified)
                // To make listing attributes less repetitive, a dollar sign
                // can be used as an implementation name to denote that it is
                // the same as the attribute.
                $attributes[$attr] = [$attr, $field === '$' ? $attr : $field];
            }

            foreach ($def['relationships'] ?? [] as $name => $r) {
                if (isset($r['using']) === false) {
                    throw new Exception(sprintf(
                        'missing using clause in %s.%s relationship', $resource, $name
                    ));
                }

                $using = $r['using'];

                unset($r['using']);

                // Wouldn't be trivial to resolve the relationship type if there
                // were more fields than necessary.
                if (count($r) > 1) {
                    throw new Exception(sprintf(
                        'malformed relationship: %s.%s', $resource, $name
                    ));
                }

                list($relationshipType) = array_keys($r);
                list($related) = array_values($r);

                if (isset($relationshipTypes[$relationshipType]) === false) {
                    throw new Exception(sprintf(
                        'undefined relationship type for %s.%s', $resource, $name
                    ));
                }

                $mask  = $relationshipTypes[$relationshipType];

                if (is_array($using)) {
                    $chain = [];

                    foreach ($using as $p) {
                        if (isset($p['relation'], $p['from'], $p['to']) === false) {
                            throw new Exception('pivot relationships must have the '
                                    . 'following fields: relation, from, to');
                        }

                        $chain[] = [$p['relation'], $p['from'], $p['to']];
                    }

                    $using = $chain;
                }

                $relationships[$name] = [$mask, $related, $using];
            }

            $typeSchemaMap[$resource] = $nschemas;

            $schemas[$nschemas++] = [
                [$resource, $source, $id], // table/primary key info
                $attributes,
                $relationships,
                $actionMap,
            ];
        }
    }

    
    // Assert all declared relationships point to a schema in the graph.
    foreach ($schemas as $schema) {
        list($def, $attributes, $relationships, $actionMap) = $schema;
        list($type, $implType, $id) = $def;

        // Relationships are defined in index 2.
        foreach ($schema[2] as $relationship => $relationshipData) {
            list($mask, $related, $using) = $relationshipData;

            if (isset($typeSchemaMap[$related]) === false) {
                throw new Exception(sprintf(
                    'schema %s referenced in relationship %s.%s is not defined in the graph',
                    $related,
                    $type,
                    $relationship
                ));
            }
        }
    }

} catch (Exception $e) {
    echo 'error: ', $e->getMessage(), PHP_EOL;
}

$cache = [$actions, $schemas];

file_put_contents($dest, sprintf("<?php return %s;", var_export($cache, true)));

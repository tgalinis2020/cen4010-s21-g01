<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use ThePetPark\Library\Graph;

if ($argc < 3) {
    printf('usage: php %s <src> <dest>', $argv[0]);
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

/** @var \ThePetPark\Library\Graph\ActionInterface[] */
$graphActions = [
    Graph\Handlers\NotImplemented::class,
];

/** @var int */
$nactions = 1;

/** @var array */
$defaultActions = [];

/** @var \ThePetPark\Library\Graph\ActionInterface[] */
$defaultActions[Graph\Graph::RESOURCE]     = [];

/** @var \ThePetPark\Library\Graph\ActionInterface[] */
$defaultActions[Graph\Graph::RELATIONSHIP] = [];

$contextTypes = [
    'resource'      => Graph\Graph::RESOURCE,
    'relationship'  => Graph\Graph::RELATIONSHIP,
];

$relationshipTypes = [
    'belongsTo'     => Graph\Relationship::OWNED|Graph\Relationship::ONE,
    'belongsToMany' => Graph\Relationship::OWNED|Graph\Relationship::MANY,
    'has'           => Graph\Relationship::OWNS|Graph\Relationship::ONE,
    'hasMany'       => Graph\Relationship::OWNS|Graph\Relationship::MANY,
];

try {

    // Get default schema actions
    if (isset($root['actions'])) {
        foreach ($contextTypes as $ctx => $type) {
            foreach ($root['actions'][$ctx] ?? [] as $httpVerb => $action) {
                $defaultActions[$type][$httpVerb] = $nactions++;
                $graphActions[] = $action;
            }
        }
    }

    if (isset($root['schemas'])) {
        foreach ($root['schemas'] as $resource => $def) {
            $id            = $def['id'] ?? 'id';
            $source        = $def['src'] ?? $resource;
            $actions       = [];
            $attributes    = [];
            $relationships = [];

            // Initialize action map using not-implemented action.
            foreach ($contextTypes as $type) {
                $actionMap[$type] = [
                    'GET'     => Graph\Graph::ACTION_NOT_IMPL,
                    'POST'    => Graph\Graph::ACTION_NOT_IMPL,
                    'PUT'     => Graph\Graph::ACTION_NOT_IMPL,
                    'PATCH'   => Graph\Graph::ACTION_NOT_IMPL,
                    'DELETE'  => Graph\Graph::ACTION_NOT_IMPL,
                ];
            }

            foreach ($contextTypes as $ctx => $type) {
                foreach ($def['actions'][$ctx] ?? [] as $httpVerb => $action) {
                    $actionMap[$type][$httpVerb] = $nactions++;
                    $graphActions[] = $action;
                }

                // Map defaults to unimplemented schema actions.
                foreach ($defaultActions[$type] as $httpVerb => $actionEnum) {
                    if ($actionMap[$type][$httpVerb] === Graph\Graph::ACTION_NOT_IMPL) {
                        $actionMap[$type][$httpVerb] = $actionEnum;
                    }
                }
            }

            foreach ($def['attributes'] ?? [] as $attr => $field) {
                // 0 = selectable value (default is 0 if no sparse fields specified)
                // To make listing attributes less repetitive, a dollar sign
                // can be used as an implementation name to denote that it is
                // the same as the attribute.
                $attributes[$attr] = [0, $attr, $field === '$' ? $attr : $field];
            }

            foreach ($def['relationships'] ?? [] as $name => $r) {
                if (isset($r['using']) === false) {
                    throw new Exception(sprintf(
                        'Missing using clause in %s.%s relationship', $resource, $name
                    ));
                }

                $using = $r['using'];

                unset($r['using']);

                // Wouldn't be trivial to resolve the relationship type if there
                // were more fields than necessary.
                if (count($r) > 1) {
                    throw new Exception(sprintf(
                        'Malformed relationship: %s.%s', $resource, $name
                    ));
                }

                list($relationshipType) = array_keys($r);
                list($related) = array_values($r);

                if (isset($relationshipTypes[$relationshipType]) === false) {
                    throw new Exception(sprintf(
                        'Missing relationship type for %s.%s', $resource, $name
                    ));
                }

                $mask  = $relationshipTypes[$relationshipType];

                if (is_array($using)) {
                    $chain = [];

                    foreach ($using as $p) {
                        if (isset($p['relation'], $p['from'], $p['to']) === false) {
                            throw new Exception('Pivot relationships must have the '
                                    . 'following fields: relation, from, to');
                        }

                        $chain[] = [$p['relation'], $p['from'], $p['to']];
                    }

                    $using = $chain;
                }

                $relationships[$name] = [$mask, $related, $using];
            }

            $schemas[] = [
                [$resource, $source, $id], // table/primary key info
                $attributes,
                $relationships,
                $actionMap,
            ];
        }
    }

} catch (Exception $e) {
    echo $argv[0], ': ', $e->getMessage(), PHP_EOL, PHP_EOL;
}

$cache = [$graphActions, $schemas];

file_put_contents($dest, sprintf("<?php return %s;", var_export($cache, true)));
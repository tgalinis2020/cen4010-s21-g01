<?php

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

(new \DI\ContainerBuilder())
    ->enableCompilation($root . '/var/cache')
    ->addDefinitions($root . '/etc/settings.php')
    ->addDefinitions($root . '/etc/slim.php')
    ->addDefinitions($root . '/etc/definitions.php')
    ->build();

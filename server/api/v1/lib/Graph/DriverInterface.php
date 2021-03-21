<?php

declare(strict_types=1);

namespace ThePetPark\Library\Graph;

interface DriverInterface
{
    public function init();
    public function select(string $resourceID);
    public function prepare(Schema\Reference $source);
    public function resolve(Schema\Reference $source, Schema\Relationship $relationship);
}
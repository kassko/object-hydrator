<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadata;

interface ClassMetadataLoaderInterface
{
    public function loadMetadata(string $class) : array;
}

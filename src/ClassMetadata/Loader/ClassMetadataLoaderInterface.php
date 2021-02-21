<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader;

use Kassko\ObjectHydrator\ClassMetadata;

interface ClassMetadataLoaderInterface
{
    public function loadMetadata(string $class, string $classUsedInConfig) : array;
}

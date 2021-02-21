<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader;

interface ClassMetadataLeafLoaderInterface
{
    public function supports(string $classUsedInConfig) : bool;
}

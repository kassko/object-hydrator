<?php

namespace Kassko\ObjectHydrator\ClassMetadataLoader;

use Kassko\ObjectHydrator\{ClassMetadata, ClassMetadataLoaderInterface, ModelBuilder};

abstract class AbstractLoader implements ClassMetadataLoaderInterface
{
    public function loadMetadata(string $class) : array
    {
        return $this->doLoadMetadata($class);
    }

    abstract protected function doLoadMetadata(string $class) : array;
}

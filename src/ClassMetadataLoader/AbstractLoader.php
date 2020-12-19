<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\{ClassMetadata, ClassMetadataLoaderInterface, ModelBuilder};

abstract class AbstractLoader implements ClassMetadataLoaderInterface
{
    public function loadMetadata(object $object) : array
    {
        return $this->doLoadMetadata($object);
    }

    abstract protected function doLoadMetadata(object $object) : array;
}

<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\{ClassMetadata, ClassMetadataLoaderInterface};

abstract class AbstractLoader implements ClassMetadataLoaderInterface
{
    public function loadMetadata(object $object) : ClassMetadata
    {
    	$classMetadata = $this->doLoadMetadata($object);
    	$classMetadata->afterMetadataLoaded();

    	return $classMetadata;
    }

    abstract protected function doLoadMetadata(object $object) : ClassMetadata;
}

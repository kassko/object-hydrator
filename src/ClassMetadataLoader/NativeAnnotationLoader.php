<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\ClassMetadata;

use function method_exists;

class NativeAnnotationLoader extends AbstractDelegatedLoader
{
    public function supports(object $object) : bool
    {
    	$metadataLocation = $this->config->getMappingValue('metadata_location', $object);

        return isset($metadataLocation['native_annotations'])
        && version_compare(\PHP_VERSION, '8.0.0') >= 0
        && (! method_exists($object, 'preferDoctrineAnnotations') || ! $object->preferDoctrineAnnotations());
    }

    protected function doLoadMetadata(object $object) : ClassMetadata;
    {
        return new ClassMetadata;
    }
}

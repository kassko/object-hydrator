<?php

namespace Kassko\ObjectHydrator\ClassMetadataLoader;

use Kassko\ObjectHydrator\ClassMetadata;

use function method_exists;

class NativeAnnotationLoader extends AbstractDelegatedLoader
{
    public function supports(string $class) : bool
    {
    	$metadataLocation = $this->config->getMappingValue('metadata_location', $class);

        return isset($metadataLocation['native_annotations'])
        && version_compare(\PHP_VERSION, '8.0.0') >= 0
        && (! method_exists($class, 'preferDoctrineAnnotations') || ! $class::preferDoctrineAnnotations());
    }

    protected function doLoadMetadata(string $class) : ClassMetadata\Model\Class_;
    {
        return new ClassMetadata;
    }
}

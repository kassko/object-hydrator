<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf;

use Kassko\ObjectHydrator\ClassMetadata;
use Kassko\ObjectHydrator\Model;

use function method_exists;

class NativeAnnotationLoader extends AbstractLoader
{
    public function supports(string $classUsedInConfig) : bool
    {
    	$metadataLocation = $this->config->getValueByNamespace($classUsedInConfig);

        return true === $metadataLocation['annotations']['enabled'] && 'native' === $metadataLocation['annotations']['type'];

        /*return isset($metadataLocation['native_annotations'])
        && version_compare(\PHP_VERSION, '8.0.0') >= 0
        && (! method_exists($classUsedInConfig, 'preferDoctrineAnnotations') || ! $classUsedInConfig::preferDoctrineAnnotations());*/
    }

    protected function doLoadMetadata(string $class, string $classUsedInConfig) : array;
    {
        return [];
    }
}

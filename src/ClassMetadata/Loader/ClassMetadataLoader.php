<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader;

use Kassko\ObjectHydrator\ClassMetadata;
use Doctrine\Common\Annotations\AnnotationReader;

class ClassMetadataLoader implements ClassMetadataLoaderInterface
{
    private LoaderResolver $loaderResolver;

    public function __construct(LoaderResolver $loaderResolver)
    {
        $this->loaderResolver = $loaderResolver;
    }

    public function loadMetadata(string $class, string $classUsedInConfig) : array
    {
        if ($classUsedInConfig !== $class && !\class_exists($classUsedInConfig)) {
            \class_alias($class, $classUsedInConfig);
        }

        return ($loader = $this->loaderResolver->resolve($classUsedInConfig))
        && ($classMetadata = $loader->loadMetadata($class, $classUsedInConfig)) ? $classMetadata : [];
    }
}

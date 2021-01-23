<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadata;
use Doctrine\Common\Annotations\AnnotationReader;

class ClassMetadataLoader implements ClassMetadataLoaderInterface
{
    private LoaderResolver $loaderResolver;

    public function __construct(LoaderResolver $loaderResolver)
    {
        $this->loaderResolver = $loaderResolver;
    }

    public function loadMetadata(string $class) : array
    {
        return ($loader = $this->loaderResolver->resolve($class))
        && ($classMetadata = $loader->loadMetadata($class)) ? $classMetadata : [];
    }
}

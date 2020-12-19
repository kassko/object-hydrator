<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadata;
use Doctrine\Common\Annotations\AnnotationReader;

class ClassMetadataLoader implements ClassMetadataLoaderInterface
{
    private LoaderResolver $loaderResolver;

    public function __construct(LoaderResolver $loaderResolver)
    {
        $this->loaderResolver = $loaderResolver;
    }

    public function loadMetadata(object $object) : array
    {
        return ($loader = $this->loaderResolver->resolve($object))
        && ($classMetadata = $loader->loadMetadata($object)) ? $classMetadata : [];
    }
}

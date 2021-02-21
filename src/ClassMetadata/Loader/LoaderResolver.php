<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader;

class LoaderResolver
{
    private iterable $loaders = [];


    public function __construct(iterable $loaders = [])
    {
        $this->loaders = $loaders;
    }

    public function resolve(string $classUsedInConfig) : ?ClassMetadataLeafLoaderInterface
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($classUsedInConfig)) {
                return $loader;
            }
        }

        return null;
    }

    public function addLoader(ClassMetadataLeafLoaderInterface $loader) : self
    {
        $this->loaders[] = $loader;

        return $this;
    }
}

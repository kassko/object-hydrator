<?php

namespace Kassko\ObjectHydrator;

class LoaderResolver
{
    private array $loaders = [];

    public function resolve(string $class) : ?ClassMetadataDelegatedLoaderInterface
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($class)) {
                return $loader;
            }
        }

        return null;
    }

    public function addLoader(ClassMetadataDelegatedLoaderInterface $loader) : self
    {
        $this->loaders[] = $loader;

        return $this;
    }
}

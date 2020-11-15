<?php

namespace Big\Hydrator;

class LoaderResolver
{
    private array $loaders = [];

    public function resolve(object $object) : ?ClassMetadataDelegatedLoaderInterface
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($object)) {
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

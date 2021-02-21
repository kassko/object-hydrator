<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader;

use Kassko\ObjectHydrator\ClassMetadata\Loader\ClassMetadataLoader;
use Kassko\ObjectHydrator\Model;
use Kassko\ObjectHydrator\ModelLoaderInterface;

class ModelLoader implements ModelLoaderInterface
{
    private ClassMetadataLoader $classMetadataLoader;
    private ModelBuilder $modelBuilder;
    private array $cache = [];

    public function __construct(ClassMetadataLoader $classMetadataLoader, ModelBuilder $modelBuilder)
    {
        $this->classMetadataLoader = $classMetadataLoader;
        $this->modelBuilder = $modelBuilder;
    }

    public function load(string $class, ?string $classUsedInConfig = null) : Model\Class_
    {
        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        $arrayMetadata = $this->classMetadataLoader->loadMetadata($class, $classUsedInConfig ?? $class);
        $classMetadata = $this->modelBuilder->setClass($class)->addConfig($arrayMetadata)->build();

        return $this->cache[$class] = $classMetadata;
    }
}

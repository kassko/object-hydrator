<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadataLoader;
use Kassko\ObjectHydrator\ModelBuilder;

class ModelLoader
{
    private ClassMetadataLoader $classMetadataLoader;
    private ModelBuilder $modelBuilder;
    private array $cache = [];

    public function __construct(ClassMetadataLoader $classMetadataLoader, ModelBuilder $modelBuilder)
    {
        $this->classMetadataLoader = $classMetadataLoader;
        $this->modelBuilder = $modelBuilder;
    }

    public function load(string $class) : ClassMetadata\Model\Class_
    {
        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        $arrayMetadata = $this->classMetadataLoader->loadMetadata($class);
        $classMetadata = $this->modelBuilder->setClass($class)->addConfig($arrayMetadata)->build();


        return $this->cache[$class] = $classMetadata;
    }
}

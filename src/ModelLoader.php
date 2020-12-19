<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadataLoader;
use Big\Hydrator\ModelBuilder;

class ModelLoader
{
    private ClassMetadataLoader $classMetadataLoader;
    private ModelBuilder $modelBuilder;

    public function __construct(ClassMetadataLoader $classMetadataLoader, ModelBuilder $modelBuilder)
    {
        $this->classMetadataLoader = $classMetadataLoader;
        $this->modelBuilder = $modelBuilder;
    }

    public function load(object $object) : ClassMetadata\Model\Class_
    {
        $arrayMetadata = $this->classMetadataLoader->loadMetadata($object);
        $classMetadata = $this->modelBuilder->setObject($object)->addConfig($arrayMetadata)->build();


        return $classMetadata;
    }

    /*public function loadMetadata(object $object) : ClassMetadata\Model\Class_
    {
        $this->loadModel($object);

        return $this->classMetadataLoader->loadMetadata($object);
    }*/
}

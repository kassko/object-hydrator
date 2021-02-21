<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader;

use Kassko\ObjectHydrator\ClassMetadata\Loader;
use Kassko\ObjectHydrator\ClassMetadata\Loader\ClassMetadataConfig;
use Kassko\ObjectHydrator\Model\Repository;

class LeafLoaderConfigurator
{
    protected ClassMetadataConfig $config;
    protected Repository\ReflectionClass $reflectionClassRepository;


    public function __construct(ClassMetadataConfig $config, Repository\ReflectionClass $reflectionClassRepository)
    {
        $this->config = $config;
        $this->reflectionClassRepository = $reflectionClassRepository;
    }

    public function configure(Loader\Leaf\AbstractLoader $leafLoader) : void
    {
        $leafLoader->setConfig($this->config);
        $leafLoader->setReflectionClassRepository($this->reflectionClassRepository);
    }
}

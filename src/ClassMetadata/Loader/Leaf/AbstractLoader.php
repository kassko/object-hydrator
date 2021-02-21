<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf;

use Kassko\ObjectHydrator\{ClassMetadata, ClassMetadata\Loader\ClassMetadataLoaderInterface};
use Kassko\ObjectHydrator\{ClassMetadata\Loader\ClassMetadataConfig, ClassMetadata\Loader\ClassMetadataLeafLoaderInterface};
use Kassko\ObjectHydrator\Model\Repository;

abstract class AbstractLoader implements ClassMetadataLoaderInterface, ClassMetadataLeafLoaderInterface
{
    protected ClassMetadataConfig $config;
    protected Repository\ReflectionClass $reflectionClassRepository;

	public function supports(string $classUsedInConfig) : bool
    {
        /**
            *  [
            *      'metadata_location' => [
            *          'yaml_file' => [
            *              'path' => 'my_path'
            *          ]
            *      ]
            *  ]
            */
        $metadataLocation = $this->config->getValueByNamespace($classUsedInConfig);
        $ressourceType = $this->getRessourceType();

        return isset($metadataLocation[$ressourceType])
        && true === $metadataLocation[$ressourceType]['enabled']
        && $this->getContentType() === $metadataLocation[$ressourceType]['type'];
    }

    public function loadMetadata(string $class, string $classUsedInConfig) : array
    {
        return $this->doLoadMetadata($class, $classUsedInConfig);
    }

    protected function getRessourceType() : string { return ''; }

    protected function getContentType() : string { return ''; }

    abstract protected function doLoadMetadata(string $class, string $classUsedInConfig) : array;

    public function getValueByPathAndNamespace(string $key, string $namespace)
    {
    	return $this->config->getValueByPathAndNamespace($key, $namespace);
    }

    public function setConfig(ClassMetadataConfig $config) : self
    {
        $this->config = $config;

        return $this;
    }

    public function setReflectionClassRepository(Repository\ReflectionClass $reflectionClassRepository) : self
    {
        $this->reflectionClassRepository = $reflectionClassRepository;

        return $this;
    }
}

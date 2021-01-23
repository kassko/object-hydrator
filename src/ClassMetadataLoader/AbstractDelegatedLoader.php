<?php

namespace Kassko\ObjectHydrator\ClassMetadataLoader;

use Kassko\ObjectHydrator\ClassMetadata\Repository;
use Kassko\ObjectHydrator\{ClassMetadataConfig, ClassMetadataDelegatedLoaderInterface};

abstract class AbstractDelegatedLoader extends AbstractLoader implements ClassMetadataDelegatedLoaderInterface
{
	protected ClassMetadataConfig $config;
    protected Repository\ReflectionClass $reflectionClassRepository;

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

    public function getValueByPathAndNamespace(string $key, string $namespace)
    {
    	return $this->config->getValueByPathAndNamespace($key, $namespace);
    }
}

<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\ClassMetadata\Repository\ReflectionClassRepository;
use Big\Hydrator\{ClassMetadataConfig, ClassMetadataDelegatedLoaderInterface};

abstract class AbstractDelegatedLoader extends AbstractLoader implements ClassMetadataDelegatedLoaderInterface
{
	protected ClassMetadataConfig $config;
    protected ReflectionClassRepository $reflectionClassRepository;

    public function setConfig(ClassMetadataConfig $config) : self
    {
        $this->config = $config;

        return $this;
    }

    public function setReflectionClassRepository(ReflectionClassRepository $reflectionClassRepository) : self
    {
        $this->reflectionClassRepository = $reflectionClassRepository;

        return $this;
    }

    public function getValueByPathAndObject(string $key, object $object)
    {
        return $this->config->getValueByPathAndObject($key, $object);
    }

    public function getValueByPathAndNamespace(string $key, string $namespace)
    {
    	return $this->config->getValueByPathAndNamespace($key, $namespace);
    }
}

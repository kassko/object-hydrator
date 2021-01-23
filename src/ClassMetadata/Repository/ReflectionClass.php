<?php
namespace Kassko\ObjectHydrator\ClassMetadata\Repository;

use Kassko\ObjectHydrator\ClassMetadata\ReflectionClass as ModelReflectionClass;

class ReflectionClass
{
	private array $reflectionClasses = [];

	public function findByObject(object $object) : ?ModelReflectionClass
    {
        return isset($this->reflectionClasses[$class = get_class($object)]) ? $this->reflectionClasses[$class] : null;
    }

	public function findByClass(string $class) : ?ModelReflectionClass
    {
        return isset($this->reflectionClasses[$class]) ? $this->reflectionClasses[$class] : null;
    }

    public function addReflectionClass(ModelReflectionClass $reflectionClass) : self
    {
        $this->reflectionClasses[$reflectionClass->getName()] = $reflectionClass;

        return $this;
    }

    public function addReflectionClassByObject(object $object) : ModelReflectionClass
    {
        return $this->reflectionClasses[get_class($object)] = ModelReflectionClass::fromObject($object);
    }

    public function addReflectionClassByClass(string $class) : ModelReflectionClass
    {
        return $this->reflectionClasses[$class] = ModelReflectionClass::fromClass($class);
    }
}

<?php
namespace Big\Hydrator\ClassMetadata\Repository;

use Big\Hydrator\ClassMetadata\ReflectionClass;

class ReflectionClassRepository
{
	private array $reflectionClasses = [];

	public function findByObject(object $object) : ?ReflectionClass
    {
        return isset($this->reflectionClasses[$class = get_class($object)]) ? $this->reflectionClasses[$class] : null;
    }

	public function findByClass(string $class) : ?ReflectionClass
    {
        return isset($this->reflectionClasses[$class]) ? $this->reflectionClasses[$class] : null;
    }

    public function addReflectionClass(ReflectionClass $reflectionClass) : self
    {
        $this->reflectionClasses[$reflectionClass->getName()] = $reflectionClass;

        return $this;
    }

    public function addReflectionClassByObject(object $object) : ReflectionClass
    {
        return $this->reflectionClasses[get_class($object)] = ReflectionClass::fromObject($object);
    }

    public function addReflectionClassByClass(string $class) : ReflectionClass
    {
        return $this->reflectionClasses[$class] = ReflectionClass::fromClass($class);
    }
}

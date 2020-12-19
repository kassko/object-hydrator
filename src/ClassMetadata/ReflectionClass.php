<?php
namespace Big\Hydrator\ClassMetadata;

final class ReflectionClass
{
    private array $reflectionClassesHierarchy = [];
    private \ReflectionClass $nativeReflectionClass;
    private array $properties = [];
    private array $methods = [];


    public static function fromObject(object $object)
    {
        return new self(new \ReflectionClass($object));
    }

    public static function fromClass(string $class)
    {
        return new self(new \ReflectionClass($class));
    }

    public static function fromNativeReflectionClass(\ReflectionClass $reflectionClass)
    {
        return new self($reflectionClass);
    }

    public function getNativeReflectionClass() : array
    {
        return $this->nativeReflectionClass;
    }

    public function getName() : string
    {
        return $this->nativeReflectionClass->getName();
    }

    public function getReflectionClassesHierarchy() : array
    {
        return $this->reflectionClassesHierarchy;
    }

    public function getProperties() : array
    {
        return $this->properties;
    }

    public function hasProperty(string $name) : bool
    {
        return isset($this->properties[$name]);
    }

    public function getProperty(string $name) : \ReflectionProperty
    {
        return $this->properties[$name];
    }

    public function getMethods() : array
    {
        return $this->methods;
    }

    public function hasMethod(string $name) : bool
    {
        return isset($this->methods[$name]);
    }

    public function getMethod(string $name) : \ReflectionMethod
    {
        return $this->methods[$name];
    }

    private function __construct(\ReflectionClass $nativeReflectionClass)
    {
        $this->nativeReflectionClass = $nativeReflectionClass;

        $this->initReflectionClassesHierarchy();
        $this->initProperties();
        $this->initMethods();
    }

    private function initReflectionClassesHierarchy() : void
    {
        $reflectionClass = $this->nativeReflectionClass;
        $this->reflectionClassesHierarchy = [$reflectionClass];

        while (false !== ($reflectionClass = $reflectionClass->getParentClass())) {
            $this->reflectionClassesHierarchy[] = $reflectionClass;
        }
    }

    private function initProperties() : void
    {
        $filter = \ReflectionMethod::IS_PUBLIC
            | \ReflectionMethod::IS_PROTECTED
            | \ReflectionMethod::IS_PRIVATE
            | \ReflectionMethod::IS_FINAL;

        foreach ($this->reflectionClassesHierarchy as $key => $reflectionClass) {
            $properties = $reflectionClass->getProperties($filter);
            $propertiesNames = $this->extractPropertiesNames($properties);
            $properties = array_combine($propertiesNames, $properties);

            $this->properties = array_merge($properties, $this->properties);
        }
    }

    private function extractPropertiesNames(array $properties) : array
    {
        $propertiesNames = [];

        foreach ($properties as $property) {
            $propertiesNames[$name = $property->getName()] = $name;
        }

        return $propertiesNames;
    }

    private function initMethods() : void
    {
        $parentsClassFilter = \ReflectionMethod::IS_PUBLIC
            | \ReflectionMethod::IS_PROTECTED
            | \ReflectionMethod::IS_FINAL;

        $childClassFilter = $parentsClassFilter | \ReflectionMethod::IS_PRIVATE;

        foreach ($this->reflectionClassesHierarchy as $key => $reflectionClass) {
            $filter = 0 === $key ? $childClassFilter : $parentsClassFilter;
            $methods = $reflectionClass->getMethods($filter);

            $methodsNames = $this->extractMethodsNames($methods);
            $methods = array_combine($methodsNames, $methods);

            $this->methods = array_merge($methods, $this->methods);
        }
    }

    private function extractMethodsNames(array $methods) : array
    {
        $methodNames = [];

        foreach ($methods as $method) {
            $methodNames[$name = $method->getName()] = $name;
        }

        return $methodNames;
    }
}

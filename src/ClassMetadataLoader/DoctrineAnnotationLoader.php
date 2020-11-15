<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\ClassMetadata;
use Doctrine\Common\Annotations\AnnotationReader;

use function get_class;
use function method_exists;
use function version_compare;

class DoctrineAnnotationLoader extends AbstractDelegatedLoader
{
    private AnnotationReader $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    public function supports(object $object) : bool
    {
        $doctrineAnnotations = $this->getValueByPathAndObject('annotations.type', $object);

        return $doctrineAnnotations
        && (version_compare(\PHP_VERSION, '8.0.0') < 0
        || (method_exists($object, 'preferDoctrineAnnotations') && $object->preferDoctrineAnnotations()));
    }

    protected function doLoadMetadata(object $object) : ClassMetadata
    {
        $classMetadata = new ClassMetadata($object);
        $reflectionClass = $classMetadata->getReflectionClass($object);

        //Class level
        $classLevelAnnotations = $this->getClassLevelAnnotationsByClass($reflectionClass);
        $classOptions = $classLevelAnnotations[ClassMetadata\ClassOptions::class] ?? new ClassMetadata\ClassOptions;

        $classMetadata->setPropertiesExcludedByDefault($classOptions->propertiesExcludedByDefault);

        if (isset($classLevelAnnotations[ClassMetadata\DataSources::class])) {
            $classMetadata->setDataSources($classLevelAnnotations[ClassMetadata\DataSources::class]);
        }

        if (isset($classLevelAnnotations[ClassMetadata\Conditionals::class])) {
            $classMetadata->setConditionals($classLevelAnnotations[ClassMetadata\Conditionals::class]);
        }

        //Property level
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            if ('__registered' === $propertyName) {
                continue;
            }

            $propertyLevelAnnotationsByClass = $this->getPropertyLevelAnnotationsByClass($reflectionProperty);

            if (isset($propertyLevelAnnotationsByClass[ClassMetadata\Property::class])) {
                foreach ($propertyLevelAnnotationsByClass[ClassMetadata\Property::class] as $property) {
                    $property = $propertyLevelAnnotationsByClass[ClassMetadata\Property::class];
                    $classMetadata->addIncludedProperty($propertyName, $property);
                }
            } elseif (isset($propertyLevelAnnotationsByClass[ClassMetadata\ExcludedProperty::class])) {
                $property = $propertyLevelAnnotationsByClass[ClassMetadata\ExcludedProperty::class];
                $classMetadata->addExcludedProperty($propertyName, $property);
            }
        }

        return $classMetadata;
    }

    private function getClassLevelAnnotationsByClass(\ReflectionClass $reflectionClass) : array
    {
        $classLevelAnnotationsByClass = [];

        $classLevelAnnotations = $this->reader->getClassAnnotations($reflectionClass);
        foreach ($classLevelAnnotations as $classLevelAnnotation) {
            if (! $classLevelAnnotation->enabled) {
                continue;
            }
            $classLevelAnnotationsByClass[get_class($classLevelAnnotation)] = $classLevelAnnotation;
        }

        return $classLevelAnnotationsByClass;
    }

    private function getPropertyLevelAnnotationsByClass(\ReflectionProperty $reflectionProperty) : array
    {
        $propertyLevelAnnotationsByClass = [];

        $propertyLevelAnnotations = $this->reader->getPropertyAnnotations($reflectionProperty);
        var_dump($propertyLevelAnnotations);
        foreach ($propertyLevelAnnotations as $propertyLevelAnnotation) {
            if (! $propertyLevelAnnotation->enabled) {
                continue;
            }

            $propertyLevelAnnotationClass = get_class($propertyLevelAnnotation);
            if (! isset($propertyLevelAnnotationsByClass[$propertyLevelAnnotationClass])) {
                $propertyLevelAnnotationsByClass[$propertyLevelAnnotationClass] = [];
            }

            $propertyLevelAnnotationsByClass[$propertyLevelAnnotationClass][] = $propertyLevelAnnotation;
        }

        return $propertyLevelAnnotationsByClass;
    }
}

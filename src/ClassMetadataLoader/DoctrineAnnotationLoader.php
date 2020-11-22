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

        $classMetadata->setDefaultHydrateAllProperties($classOptions->defaultHydrateAllProperties);

        if (isset($classLevelAnnotations[ClassMetadata\DataSources::class])) {
            $dataSources = new ClassMetadata\DataSources;
            foreach ($classLevelAnnotations[ClassMetadata\DataSources::class]->items as $dataSource) {
                if ($dataSource->enabled) {
                    $dataSources->items[] = $dataSource;
                }
            }

            $classMetadata->setDataSources($dataSources);
        }

        if (isset($classLevelAnnotations[ClassMetadata\Conditionals::class])) {
            $conditionals = new ClassMetadata\Conditionals;
            foreach ($classLevelAnnotations[ClassMetadata\Conditionals::class]->items as $conditional) {
                if ($conditional->enabled) {
                    $conditionals->items[] = $conditional;
                }
            }

            $classMetadata->setConditionals($conditionals);
        }

        //Property level
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            if ('__registered' === $propertyName) {
                continue;
            }

            $propertyLevelAnnotationsByClass = $this->getPropertyLevelAnnotationsByClass($reflectionProperty);

            $propertyCandidates = new ClassMetadata\PropertyCandidates;
            $propertyCandidates->name = $propertyName;

            if (isset($propertyLevelAnnotationsByClass[ClassMetadata\Property::class])) {
                $propertyCandidates->items[] = $propertyLevelAnnotationsByClass[ClassMetadata\Property::class];
            }

            if (isset($propertyLevelAnnotationsByClass[ClassMetadata\PropertyCandidates::class])) {
                if (count($propertyCandidates->items) > 0) {
                    throw new \LogicException(sprintf(
                        'Cannot load doctrine annotations.' .
                        'You must annotate property "%s" with either annotation "%s" or "%s" but not both.' .
                        'Please fix this by removing or disabling (attribute "enabled" to false) one of these annotations.',
                        $propertyName,
                        ClassMetadata\Property::class,
                        ClassMetadata\PropertyCandidates::class
                    ));
                }


                foreach ($propertyLevelAnnotationsByClass[ClassMetadata\PropertyCandidates::class] as $candidateProperty) {
                    if ($candidateProperty->enabled) {
                        $propertyCandidates->name = $propertyName;
                        $propertyCandidates->items[] = $candidateProperty;
                        $propertyCandidates->variables = $propertyLevelAnnotationsByClass[ClassMetadata\PropertyCandidates::class]->variables;
                    }
                }
            }

            if (count($propertyCandidates->items) > 0) {
                $classMetadata->addExplicitlyIncludedProperty($propertyName, $propertyCandidates);
            } elseif (isset($propertyLevelAnnotationsByClass[ClassMetadata\ExcludedProperty::class])) {
                $property = $propertyLevelAnnotationsByClass[ClassMetadata\ExcludedProperty::class];
                $classMetadata->addExplicitlyExcludedProperty($propertyName, $property);
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
        foreach ($propertyLevelAnnotations as $propertyLevelAnnotation) {
            if (! $propertyLevelAnnotation->enabled) {
                continue;
            }
            $propertyLevelAnnotationsByClass[get_class($propertyLevelAnnotation)] = $propertyLevelAnnotation;
        }

        return $propertyLevelAnnotationsByClass;
    }
}

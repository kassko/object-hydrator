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
            foreach ($classLevelAnnotations[ClassMetadata\DataSources::class] as $dataSource) {
                if ($dataSource->enabled) {
                    $classMetadata->addDataSource($dataSource);
                }
            }
        }

        if (isset($classLevelAnnotations[ClassMetadata\Conditionals::class])) {
            foreach ($classLevelAnnotations[ClassMetadata\Conditionals::class] as $conditional) {
                if ($conditional->enabled) {
                    $classMetadata->addConditional($conditional);
                }
            }
        }

        //Property level
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            if ('__registered' === $propertyName) {
                continue;
            }

            $propertyLevelAnnotationsByClass = $this->getPropertyLevelAnnotationsByClass($reflectionProperty);

            $candidateProperties = new ClassMetadata\CandidateProperties;
            $candidateProperties->name = $propertyName;

            if (isset($propertyLevelAnnotationsByClass[ClassMetadata\Property::class])) {
                $candidateProperties->items[] = $propertyLevelAnnotationsByClass[ClassMetadata\Property::class];
            }

            if (isset($propertyLevelAnnotationsByClass[ClassMetadata\CandidateProperties::class])) {
                if (count($candidateProperties->items) > 0) {
                    throw new \LogicException(sprintf(
                        'Cannot load doctrine annotations.' .
                        'You must annotate property "%s" with either annotation "%s" or "%s" but not both.' .
                        'Please fix this by removing or disabling (attribute "enabled" to false) one of these annotations.',
                        $propertyName,
                        ClassMetadata\Property::class,
                        ClassMetadata\CandidateProperties::class
                    ));
                }

                $candidateProperties->variables = $propertyLevelAnnotationsByClass[ClassMetadata\CandidateProperties::class]->variables;

                foreach ($propertyLevelAnnotationsByClass[ClassMetadata\CandidateProperties::class] as $candidateProperty) {
                    if ($candidateProperty->enabled) {
                        $candidateProperties->items[] = $candidateProperty;
                    }
                }
            }

            if (count($candidateProperties->items) > 0) {
                $property = $propertyLevelAnnotationsByClass[ClassMetadata\Property::class];
                $classMetadata->addExplicitlyIncludedProperty($propertyName, $candidateProperties);
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

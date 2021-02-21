<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf;

use Kassko\ObjectHydrator\ClassMetadata;
use Kassko\ObjectHydrator\Annotation\Doctrine as Annotation;
use Doctrine\Common\Annotations\AnnotationReader;

use function get_class;
use function method_exists;
use function version_compare;

class DoctrineAnnotationLoader extends AbstractLoader
{
    private AnnotationReader $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    public function supports(string $classUsedInConfig) : bool
    {
        $metadataLocation = $this->config->getValueByNamespace($classUsedInConfig);

        return true === $metadataLocation['annotations']['enabled'] && 'doctrine' === $metadataLocation['annotations']['type'];

        /*return $doctrineAnnotations
        && (version_compare(\PHP_VERSION, '8.0.0') < 0
        || (method_exists($classUsedInConfig, 'preferDoctrineAnnotations') && $classUsedInConfig::preferDoctrineAnnotations()));*/
    }

    protected function doLoadMetadata(string $class, string $classUsedInConfig) : array
    {
        $classMetadata = [];

        $reflectionClass = $this->reflectionClassRepository->addReflectionClassByClass($class);

        $classLevelAnnotations = $this->getClassLevelAnnotationsByClass($reflectionClass->getReflectionClassesHierarchy());

        if (isset($classLevelAnnotations[Annotation\ClassConfig::class])) {
            $classMetadata['class'] = $classLevelAnnotations[Annotation\ClassConfig::class]->toArray();
        }

        if (isset($classLevelAnnotations[Annotation\Method::class])) {
            $classMetadata['method'] = $classLevelAnnotations[Annotation\Method::class]->toArray();
        }

        if (isset($classLevelAnnotations[Annotation\Methods::class])) {
            $classMetadata['methods'] = $classLevelAnnotations[Annotation\Methods::class]->toArray()['items'];
        }

        if (isset($classLevelAnnotations[Annotation\Expression::class])) {
            $classMetadata['expression'] = $classLevelAnnotations[Annotation\Expression::class]->toArray();
        }

        if (isset($classLevelAnnotations[Annotation\Expressions::class])) {
            $classMetadata['expressions'] = $classLevelAnnotations[Annotation\Expressions::class]->toArray()['items'];
        }

        if (isset($classLevelAnnotations[Annotation\DataSource::class])) {
            $classMetadata['data_source'] = $classLevelAnnotations[Annotation\DataSource::class]->toArray();
        }

        if (isset($classLevelAnnotations[Annotation\DataSources::class])) {
            $classMetadata['data_sources'] = $classLevelAnnotations[Annotation\DataSources::class]->toArray()['items'];
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            if ('__registered' === $propertyName) {
                continue;
            }

            $propertyLevelAnnotationsByClass = $this->getPropertyLevelAnnotationsByClass($reflectionProperty);

            if (isset($propertyLevelAnnotationsByClass[Annotation\PropertyConfig\SingleType::class])) {
                $classMetadata['properties'][$propertyName]['single_type'] =
                    $propertyLevelAnnotationsByClass[Annotation\PropertyConfig\SingleType::class]->toArray();


                self::removeArrayDimension(
                    $classMetadata, [
                        'methods' => 'items',
                        'before_collection' => 'items',
                        'after_collection' => 'items',
                        'after_construction_methods' => 'items',
                    ]
                );
            }

            if (isset($propertyLevelAnnotationsByClass[Annotation\PropertyConfig\CollectionType::class])) {
                $propertyData = $propertyLevelAnnotationsByClass[Annotation\PropertyConfig\CollectionType::class]->toArray();

                if (isset($propertyData['item_class_candidates'])) {
                    $propertyData['item_class_candidates'] = $propertyData['item_class_candidates']['items'];
                }

                $classMetadata['properties'][$propertyName]['collection_type'] = $propertyData;
            }

            if (isset($propertyLevelAnnotationsByClass[Annotation\PropertyConfig\Candidates::class])) {
                $propertyCandidatesData = $propertyLevelAnnotationsByClass[Annotation\PropertyConfig\Candidates::class]->toArray();
                foreach ($propertyCandidatesData['candidates'] as $key => &$propertyCandidateData) {
                    if (!isset($propertyCandidateData['items_class'])) {
                        $propertyCandidateData = ['single_type' => $propertyCandidateData];
                    } else {
                        if (isset($propertyCandidateData['item_class_candidates'])) {
                            $propertyCandidateData['item_class_candidates'] = $propertyCandidateData['item_class_candidates']['items'];

                        }
                        $propertyCandidateData = ['collection_type' => $propertyCandidateData];
                    }
                }

                $classMetadata['properties'][$propertyName]['candidates'] = $propertyCandidatesData;
            }

            if (isset($propertyLevelAnnotationsByClass[Annotation\NotToAutoconfigure::class])) {
                $classMetadata['not_to_autoconfigure_properties'][] = $propertyName;
            }

            /*var_dump($classMetadata['properties'][$propertyName]['single_type']);
            if (isset($propertyLevelAnnotationsByClass['callbacks_using_metadata'])) {
                $callbacksData = &$propertyLevelAnnotationsByClass['callbacks_using_metadata'];

                var_dump($callbacksData['beforeCollection']['items']);
                if (isset($callbacksData['beforeCollection']['items'])) {
                    $callbacksData['beforeCollection'] = $callbacksData['beforeCollection']['items'];
                }

                if (isset($callbacksData['afterCollection']['items'])) {
                    $callbacksData['afterCollection'] = $callbacksData['afterCollection']['items'];
                }
                //var_dump($callbacksData);
            }*/
        }

        return $classMetadata;
    }

    public static function removeArrayDimension(array &$array, array $criteria)
    {
        foreach ($array as $key => &$item) {
            foreach ($criteria as $keyToWorkOn => $childKeyToRemove) {
                if ($key === $keyToWorkOn && isset($item[$childKeyToRemove])) {
                    $item = array_merge($item, $item[$childKeyToRemove]);
                    unset($item[$childKeyToRemove]);
                    break;
                }
            }

            if (is_array($item)) {
                self::removeArrayDimension($item, $criteria);
            }
        }
    }

    private function getClassLevelAnnotationsByClass(array $reflectionClassesHierarchy) : array
    {
        $classLevelAnnotationsByClass = [];
        $previousReflectionClass = null;

        foreach ($reflectionClassesHierarchy as $reflectionClass) {
            $classLevelAnnotations = $this->reader->getClassAnnotations($reflectionClass);
            foreach ($classLevelAnnotations as $classLevelAnnotation) {
                if ($classLevelAnnotation->ignore) {
                    continue;
                }

                $classLevelAnnotationClass = get_class($classLevelAnnotation);

                if (isset($classLevelAnnotationsByClass[$classLevelAnnotationClass])) {
                    throw new \Exception(sprintf(
                        'Cannot define class annotation "%s" on both class "%s" and its parent class "%s".',
                        $classLevelAnnotationClass,
                        $previousReflectionClass,
                        $reflectionClass->getName()
                    ));
                }
                $classLevelAnnotationsByClass[$classLevelAnnotationClass] = $classLevelAnnotation;
            }

            $previousReflectionClass = $reflectionClass;
        }

        return $classLevelAnnotationsByClass;
    }

    private function getPropertyLevelAnnotationsByClass(\ReflectionProperty $reflectionProperty) : array
    {
        $propertyLevelAnnotationsByClass = [];

        $propertyLevelAnnotations = $this->reader->getPropertyAnnotations($reflectionProperty);
        foreach ($propertyLevelAnnotations as $propertyLevelAnnotation) {
            if ($propertyLevelAnnotation->ignore) {
                continue;
            }
            $propertyLevelAnnotationsByClass[get_class($propertyLevelAnnotation)] = $propertyLevelAnnotation;
        }

        return $propertyLevelAnnotationsByClass;
    }
}

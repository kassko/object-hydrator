<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\PropertyCandidatesResolverAwareTrait;

/**
 * EssentialDataProvider
 *
 * @author kko
 */
class EssentialDataProvider
{
    use PropertyCandidatesResolverAwareTrait;

    private object $object;
    private ClassMetadata\Model\Class_ $classMetadata;
    private DataFetcher $dataFetcher;
    private MemberAccessStrategyFactory $memberAccessStrategyFactory;
    private ?\Closure $serviceLocator = null;

    public function __construct(
        DataFetcher $dataFetcher,
        MemberAccessStrategyFactory $memberAccessStrategyFactory,
        ?\Closure $serviceLocator = null
    ) {
        $this->dataFetcher = $dataFetcher;
        $this->memberAccessStrategyFactory = $memberAccessStrategyFactory;
        $this->serviceLocator = $serviceLocator;
    }

    public function withContext(object $object, ClassMetadata\Model\Class_ $classMetadata) : self
    {
        $this->object = $object;
        $this->classMetadata = $classMetadata;

        return $this;
    }

    public function getPropertyValue(string $propertyName)
    {
        $property = $this->classMetadata->getProperty($propertyName);

        if ($property->hasCandidates()) {
            throw new \Exception(sprintf(
                'Cannot get property value.'
                . PHP_EOL . 'Property "%s" is a collection of candidates but candidates are forbidden here. A resolved property is required.',
                $propertyName
            ));
        }

        return $this->memberAccessStrategyFactory->property($this->object, $this->classMetadata)->getValue($property);
    }

    public function loadPropertyAndGetValue(string $propertyName)
    {
        $property = $this->classMetadata->getProperty($propertyName);

        return $this->memberAccessStrategyFactory->getterSetter($this->object, $this->classMetadata)->getValue($property);
    }

    public function resolveService(string $serviceKey)
    {
        if (! isset($this->serviceLocator)) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot locate a provider (container, factory ...) for service key "%s".'
                    . PHP_EOL . 'You must provide a service locator throw config key "psr_container" or "service_provider".',
                    $serviceKey
                )
            );
        }

        return ($this->serviceLocator)($serviceKey);
    }

    public function fetchDataSource(string $dataSourceId) : void
    {
        $this->dataFetcher->fetchDataFromDataSource(
            $this->classMetadata->findDataSource($dataSourceId),
            $this->object,
            $this->classMetadata
        );
    }

    public function fetchDataSourcesByTag(string $dataSourceTag) : void
    {
        $this->dataFetcher->fetchDataFromDataSource(
            $this->classMetadata->findDataSourcesByTag($dataSourceTag),
            $this->object,
            $this->classMetadata
        );
    }

    public function arrayKeyExists(string $key, array $array) : bool
    {
        return false !== $this->findValueByPath($key, $array);
    }

    public function arrayKeyIsSet(string $key, array $array) : bool
    {
        $valueInPath = $this->findValueByPath($key, $array);

        return isset($valueInPath);
    }

    public function arrayIsCollectionOfItems(array $array) : bool
    {
        return null !== ($key = key($array))
        && is_numeric($key)
        && false !== ($value = current($array))
        && is_array($value);
    }

    public function arrayHasPair(string $key, $value, array $array) : bool
    {
        $valueInPath = $this->findValueByPath($key, $array);

        return $value == $valueInPath;
    }

    public function arrayHasPairStrict(string $key, $value, array $array) : bool
    {
        $valueInPath = $this->findValueByPath($key, $array);

        return $value === $valueInPath;
    }

    public function arrayKeysExists(string $keys, array $array) : bool
    {
        $flatArray = $this->flattenArray($flatArray);

        return 0 === count(array_diff($keys, array_keys($flatArray)));
    }

    public function findValueByPath(string $path, array $array)
    {
        $pathSeparator = '.';

        $firstPathSegment = (false === $pathSeparatorPos = strpos($path, $pathSeparator))
            ? $path
            : substr($path, 0, $pathSeparatorPos);

        if (!array_key_exists($firstPathSegment, $array)) {
            return false;
        }

        if (false === $pathSeparatorPos) {
            return $array[$firstPathSegment];
        }

        return $this->findValueByPath(substr($path, $pathSeparatorPos + \strlen($pathSeparator)), $array[$firstPathSegment]);
    }

    public function flattenArray($items = null, string $currentPath = '') : array
    {
        $pathSeparator = '.';

        $flat = [];

        if (is_null($items)) {
            $items = $this->items;
        }

        foreach ($items as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $flat = array_merge(
                    $flat,
                    $this->flatten($value, $currentPath.$key.$pathSeparator)
                );
            } else {
                $flat[$currentPath.$key] = $value;
            }
        }

        return $flat;
    }
}

<?php

namespace Big\Hydrator;

/**
 * EssentialDataProvider
 *
 * @author kko
 */
class EssentialDataProvider
{
    private object $object;
    private ClassMetadata $classMetadata;
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

    public function withContext(object $object, ClassMetadata $classMetadata) : self
    {
        $this->object = $object;
        $this->classMetadata = $classMetadata;

        return $this;
    }

    public function getPropertyValue(string $propertyName)
    {
        return $this->memberAccessStrategyFactory->property($this->object, $this->classMetadata)->getValue($this->object, $propertyName);
    }

    public function loadPropertyAndGetValue(string $propertyName)
    {
        //var_dump(__FUNCTION__, $propertyName);
        return $this->memberAccessStrategyFactory->getterSetter($this->object, $this->classMetadata)->getValue($this->object, $propertyName);
    }

    public function resolveService(string $serviceKey)
    {
        if (! isset($this->serviceLocator)) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot locate a provider (container, factory ...) for service key "%s". '
                    . 'You must provide a service locator throw config => "HydratorStarter::config()". ',
                    $serviceKey
                )
            );
        }

        return ($this->serviceLocator)($serviceKey);
    }

    public function fetchDataSource(string $dataSourceId)
    {
        $this->dataFetcher->fetchDataFromDataSource(
            $this->classMetadata->findDataSource($dataSourceId),
            $this->object,
            $this->classMetadata
        );
    }

    public function fetchDataSourcesByTag(string $dataSourceTag)
    {
        $this->dataFetcher->fetchDataFromDataSource(
            $this->classMetadata->findDataSourcesByTag($dataSourceId),
            $this->object,
            $this->classMetadata
        );
    }
}

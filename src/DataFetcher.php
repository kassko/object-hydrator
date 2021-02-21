<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadata;
use Kassko\ObjectHydrator\Observer;

class DataFetcher
{
    private MethodInvoker $methodInvoker;
    private Observer\HydratorProcessingObserverManager $hydratorProcessingObserverManager;

    public function __construct(
        MethodInvoker $methodInvoker,
        Observer\HydratorProcessingObserverManager $hydratorProcessingObserverManager
    ) {
        $this->methodInvoker = $methodInvoker;
        $this->hydratorProcessingObserverManager = $hydratorProcessingObserverManager;
    }

    public function fetchDataSetByProperty(
        Model\Property\Leaf $propertyOptionsMetadata,
        object $object,
        Model\Class_ $classMetadata
    ) {
        $data = [];
        $indexedByPropertiesKeys = true;

        if ($propertyOptionsMetadata->hasDataSource()) {
            $dataSourceMetadata = $propertyOptionsMetadata->getDataSource();
            $data = $this->fetchDataFromDataSource($dataSourceMetadata, $object, $classMetadata, $propertyOptionsMetadata->getName());
            $indexedByPropertiesKeys = $dataSourceMetadata->isIndexedByPropertiesKeys();
        }

        return $indexedByPropertiesKeys ? $data : [$propertyOptionsMetadata->getKeyInRawData() => $data];
    }

    /*private function fetchDataFromDataSources(array $dataSourcesMetadata, object $object, Model\Class_ $classMetadata)
    {
        $dataByDataSources = [];

        foreach ($dataSourcesMetadata as $dataSourceMetadata) {
            $dataByDataSources[$dataSourceMetadata->getId()] = $this->fetchDataFromDataSource($dataSourceMetadata, $object, $classMetadata);
        }

        return $dataByDataSources;
    }*/

    private function fetchDataFromDataSource(
        Model\DataSource $dataSourceMetadata,
        object $object,
        Model\Class_ $classMetadata,
        string $propertyName
    ) {
        try {
            $data = null;

            $beforeUsingDataSourceMetadataDto = Observer\Dto\DataSource\BeforeUsingMetadata::from(
                $dataSourceMetadata,
                $classMetadata->getName(),
                $propertyName
            );
            $this->hydratorProcessingObserverManager->dataSourceBeforeUsingMetadata($dataSourceMetadata, $beforeUsingDataSourceMetadataDto);


            $beforeFetchingDataDto = Observer\Dto\DataSource\BeforeFetchingData::from(
                $dataSourceMetadata->getId(),
                get_class($object),
                $propertyName
            );
            $this->hydratorProcessingObserverManager->dataSourceBeforeFetchingData($dataSourceMetadata, $beforeFetchingDataDto);

            $data = $beforeFetchingDataDto->getRawData();

            if (! $dataSourceMetadata->hasFallBackDataSource()) {
                return $this->invokeDataSource($dataSourceMetadata, $classMetadata);
            }

            if (Model\DataSource::ON_FAIL_CHECK_RETURN_VALUE === $dataSourceMetadata->getOnFail()) {
                $data = $this->invokeDataSource($dataSourceMetadata, $classMetadata);

                if ($dataSourceMetadata->areDataInvalid($data)) {
                    $dataSourceMetadata = $classMetadata->findSourceById($dataSourceMetadata->getFallbackSourceId());
                    return $this->fetchDataFromDataSource($dataSourceMetadata, $object, $classMetadata, $propertyName);
                }

                return $data;
            }

            //Else Model\Source::ON_FAIL_CHECK_EXCEPTION === $dataSource->getOnFail().
            try {
                $data = $this->invokeDataSource($dataSourceMetadata, $classMetadata);
            } catch (\Exception $e) {
                $exceptionClass = $dataSourceMetadata->getExceptionClass();
                if (! $e instanceof $exceptionClass) {
                    throw $e;
                }

                $fallBackDataSourceMetadata = $classMetadata->findSourceById($dataSourceMetadata->getFallbackDataSource()->getId());
                return $this->fetchDataFromDataSource($fallBackDataSourceMetadata, $object, $classMetadata, $propertyName);
            }
        } finally {
            var_dump(__METHOD__, 'finally');

            $afterFetchingDataDto = Observer\Dto\DataSource\AfterFetchingData::from(
                $data,
                $dataSourceMetadata->getId(),
                get_class($object),
                $propertyName
            );
            $this->hydratorProcessingObserverManager->dataSourceAfterFetchingData($dataSourceMetadata, $afterFetchingDataDto);
            $data = $afterFetchingDataDto->getRawData();

            $dataSourceAfterUsingMetadataDto = Observer\Dto\DataSource\AfterUsingMetadata::from(
                $dataSourceMetadata,
                $classMetadata->getName(),
                $propertyName
            );
            $this->hydratorProcessingObserverManager->dataSourceAfterUsingMetadata($dataSourceMetadata, $dataSourceAfterUsingMetadataDto);
        }

        var_dump(__METHOD__, 'return');
        return $data;
    }

    private function invokeDataSource(Model\DataSource $dataSourceMetadata)
    {
        return $this->methodInvoker->invokeMethod($dataSourceMetadata->getMethod());
    }
}

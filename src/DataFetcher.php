<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadata;

class DataFetcher
{
    private MethodInvoker $methodInvoker;

    public function __construct(MethodInvoker $methodInvoker)
    {
        $this->methodInvoker = $methodInvoker;
    }

    public function fetchDataSetByProperty(ClassMetadata\Property $propertyOptionsMetadata, object $object, ClassMetadata $classMetadata)
    {
        $data = [];
        $indexedByPropertiesKeys = true;

        if ($propertyOptionsMetadata->hasDataSource()) {
            $dataSourceMetadata = $propertyOptionsMetadata->getDataSource();
            $data = $this->fetchDataFromDataSource($dataSourceMetadata, $object, $classMetadata);
            $indexedByPropertiesKeys = $dataSourceMetadata->isIndexedByPropertiesKeys();
        }

        return $indexedByPropertiesKeys ? $data : [$propertyOptionsMetadata->getKeyInRawData() => $data];
    }

    private function fetchDataFromDataSources(array $dataSourcesMetadata, object $object, ClassMetadata $classMetadata)
    {
        $dataByDataSources = [];

        foreach ($dataSourcesMetadata as $dataSourceMetadata) {
            $dataByDataSources[$dataSourceMetadata->getId()] = $this->fetchDataFromDataSource($dataSourceMetadata, $object, $classMetadata);
        }

        return $dataByDataSources;
    }

    private function fetchDataFromDataSource(ClassMetadata\DataSource $dataSourceMetadata, object $object, ClassMetadata $classMetadata)
    {
        $this->methodInvoker->invokeVisitorsCallbacks($dataSourceMetadata->getAfterMetadataLoading(), $dataSourceMetadata);
        $this->methodInvoker->invokeVisitorsCallbacks($dataSourceMetadata->getBeforeDataFetching());

        if (! $dataSourceMetadata->hasFallBackDataSource()) {
            return $this->invokeDataSource($dataSourceMetadata, $classMetadata);
        }

        if (ClassMetadata\DataSource::ON_FAIL_CHECK_RETURN_VALUE === $dataSourceMetadata->getOnFail()) {

            $data = $this->invokeDataSource($dataSourceMetadata, $classMetadata);

            if ($dataSourceMetadata->areDataInvalid($data)) {
                $dataSourceMetadata = $classMetadata->findSourceById($dataSourceMetadata->getFallbackSourceId());
                return $this->fetchDataFromDataSource($dataSourceMetadata, $object, $classMetadata);
            }

            return $data;
        }

        //Else ClassMetadata\Model\Source::ON_FAIL_CHECK_EXCEPTION === $dataSource->getOnFail().
        try {
            $data = $this->invokeDataSource($dataSourceMetadata, $classMetadata);
        } catch (\Exception $e) {
            $exceptionClass = $dataSourceMetadata->getExceptionClass();
            if (! $e instanceof $exceptionClass) {
                throw $e;
            }

            $fallBackDataSourceMetadata = $classMetadata->findSourceById($dataSourceMetadata->getFallbackDataSource()->getId());
            return $this->fetchDataFromDataSource($fallBackDataSourceMetadata, $object, $classMetadata);
        }

        $event = $this->methodInvoker->invokeVisitorsCallbacks($dataSourceMetadata->getAfterDataFetching(), new Event\AfterDataFetching($data));

        return $event->getNormalizedValue();
    }

    private function invokeDataSource(ClassMetadata\DataSource $dataSourceMetadata)
    {
        return $this->methodInvoker->invokeMethod($dataSourceMetadata->getMethod());
    }
}

<?php

namespace Big\Hydrator\ClassMetaData\Repository;

use Big\Hydrator\ClassMetadata\Model;

/**
 * @author kko
 */
class DataSource
{
    private array $dataSources = [];

    public function find(string $id) : ?Model\DataSource
    {
        return isset($this->dataSources[$id]) ? $this->dataSources[$id] : null;

        //throw new \LogicException(sprintf('Cannot find datasource "%s".', $id));
    }

    public function findByTag(string $tag) : array
    {
        $dataSources = [];

        foreach ($this->dataSources as $dataSource) {
            if ($tag === $dataSource->getTag()) {
                $dataSources[$dataSource->getId()] = $dataSource;
            }
        }

        return $dataSources;
    }

    public function add(Model\DataSource $dataSource) : self
    {
        $this->dataSources[$dataSource->getId()] = $dataSource;

        return $this;
    }

    public function addCollection(array $dataSources) : self
    {
        foreach ($dataSources as $dataSource) {
            $this->add($dataSource);
        }

        return $this;
    }
}

<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf;

class PhpFileLoader extends AbstractLoader
{
    protected function getRessourceType() : string
    {
        return 'file_ressource';
    }

    protected function getContentType() : string
    {
        return 'php';
    }

    protected function doLoadMetadata(string $class, string $classUsedInConfig) : array
    {
        $filePath = $this->config->getValueByNamespace($classUsedInConfig)['file_ressource']['file_path'];
        $arrayContent = file_get_contents($filePath);

        return $arrayContent;
    }
}

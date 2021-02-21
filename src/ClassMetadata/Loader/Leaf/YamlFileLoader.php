<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf;

use Symfony\Component\Yaml\Parser;

class YamlFileLoader extends AbstractLoader
{
    protected function getRessourceType() : string
    {
        return 'file_ressource';
    }

    protected function getContentType() : string
    {
        return 'yaml';
    }

    protected function doLoadMetadata(string $class, string $classUsedInConfig) : array
    {
        $filePath = $this->config->getValueByNamespace($classUsedInConfig)['file_ressource']['file_path'];
        $yamlContent = file_get_contents($filePath);
        $arrayContent = (new Parser())->parse($yamlContent);

        return $arrayContent;
    }
}


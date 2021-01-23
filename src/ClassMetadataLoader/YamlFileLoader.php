<?php

namespace Kassko\ObjectHydrator\ClassMetadataLoader;

use Kassko\ObjectHydrator\ClassMetadata;
use Symfony\Component\Yaml\Parser;

class YamlFileLoader extends AbstractPhpArrayContentLoader
{
    public function supports(string $class) : bool
    {
        /**
            *  [
            *      'metadata_location' => [
            *          'yaml_file' => [
            *              'path' => 'my_path'
            *          ]
            *      ]
            *  ]
            */
        $metadataLocation = $this->config->getMappingValue('metadata_location', $class);

        return isset($metadataLocation['yaml_file']);
    }

    protected function doLoadMetadata(string $class) : ClassMetadata\Model\Class_
    {
        $filePath = $this->config->getMappingValue('metadata_location', $class)['yaml_file']['path'];
        $yamlContent = file_get_contents($filePath);
        $arrayContent = (new Parser())->parse($yamlContent);

        return $this->loadMetadataFromContent($arrayContent);
    }
}


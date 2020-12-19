<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\ClassMetadata;
use Symfony\Component\Yaml\Parser;

class YamlFileLoader extends AbstractPhpArrayContentLoader
{
    public function supports(object $object) : bool
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
        $metadataLocation = $this->config->getMappingValue('metadata_location', $object);

        return isset($metadataLocation['yaml_file']);
    }

    protected function doLoadMetadata(object $object) : ClassMetadata\Model\Class_
    {
        $filePath = $this->config->getMappingValue('metadata_location', $object)['yaml_file']['path'];
        $yamlContent = file_get_contents($filePath);
        $arrayContent = (new Parser())->parse($yamlContent);

        return $this->loadMetadataFromContent($arrayContent);
    }
}


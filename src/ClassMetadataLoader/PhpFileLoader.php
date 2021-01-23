<?php

namespace Kassko\ObjectHydrator\ClassMetadataLoader;

use Kassko\ObjectHydrator\ClassMetadata;

class PhpFileLoader extends AbstractPhpArrayContentLoader
{
    public function supports(string $class) : bool
    {
        /**
          *  [
          *      'metadata_location' => [
          *          'php_file' => [
          *              'path' => 'my_path'
          *          ]
          *      ]
          *  ]
          */
        $metadataLocation = $this->config->getMappingValue('metadata_location', $class);

        return isset($metadataLocation['php_file']);
    }

    protected function doLoadMetadata(string $class) : ClassMetadata\Model\Class_
    {
        $filePath = $this->config->getMappingValue('metadata_location', $class)['php_file']['path'];
        $content = require $filePath;

        return $this->loadMetadataFromContent($content);
    }
}

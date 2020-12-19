<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\ClassMetadata;

class PhpFileLoader extends AbstractPhpArrayContentLoader
{
    public function supports(object $object) : bool
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
        $metadataLocation = $this->config->getMappingValue('metadata_location', $object);

        return isset($metadataLocation['php_file']);
    }

    protected function doLoadMetadata(object $object) : ClassMetadata\Model\Class_
    {
        $filePath = $this->config->getMappingValue('metadata_location', $object)['php_file']['path'];
        $content = require $filePath;

        return $this->loadMetadataFromContent($content);
    }
}

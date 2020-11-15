<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\ClassMetadata;
use Symfony\Component\Yaml\Parser;

class InnerYamlLoader extends AbstractPhpArrayContentLoader
{
    public function supports(object $object) : bool
    {
        /**
            *  [
            *      'metadata_location' => [
            *          'inner_yaml' => [
            *              'method' => 'my_path'
            *          ]
            *      ]
            *  ]
            */
        $metadataLocation = $this->config->getMappingValue('metadata_location', $object);

        return isset($metadataLocation['inner_yaml']);
    }

    protected function doLoadMetadata(object $object) : ClassMetadata
    {
        $config = $this->config->getMappingValue('metadata_location', $object)['inner_yaml'];
        $method = $config['method'];

        if (isset($config['service'])) {
            $serviceInstance = $config['service'];
            $yamlContent = $serviceInstance->$method();
        } elseif (isset($config['class'])) {
            $class = $config['class'];
            $yamlContent = (new $class)->$method();
        } elseif (isset($config['static_class'])) {
            $staticClass = $config['static_class'];
            $yamlContent = $staticClass::$method();
        } else {
            $yamlContent = $object->$method();
        }

        $arrayContent = (new Parser())->parse($yamlContent);

        return $this->loadMetadataFromContent($arrayContent);
    }
}


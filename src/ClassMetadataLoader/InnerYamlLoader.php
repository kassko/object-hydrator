<?php

namespace Kassko\ObjectHydrator\ClassMetadataLoader;

use Kassko\ObjectHydrator\ClassMetadata;
use Symfony\Component\Yaml\Parser;

class InnerYamlLoader extends AbstractPhpArrayContentLoader
{
    public function supports(string $class) : bool
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
        $metadataLocation = $this->config->getMappingValue('metadata_location', $class);

        return isset($metadataLocation['inner_yaml']);
    }

    protected function doLoadMetadata(string $class) : ClassMetadata\Model\Class_
    {
        $config = $this->config->getMappingValue('metadata_location', $class)['inner_yaml'];
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
        } else {//throw an exception
            //$yamlContent = $class::$method();
        }

        $arrayContent = (new Parser())->parse($yamlContent);

        return $this->loadMetadataFromContent($arrayContent);
    }
}


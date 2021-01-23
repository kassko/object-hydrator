<?php

namespace Kassko\ObjectHydrator\ClassMetadataLoader;

use Kassko\ObjectHydrator\ClassMetadata;

class InnerPhpLoader extends AbstractDelegatedLoader
{
    public function supports(string $class) : bool
    {
        /**
          *  [
          *      'metadata_location' => [
          *          'inner_php' => [
          *              'function': 'my_function'
          *          ]
          *      ]
          *  ]
          */
        $metadataLocation = $this->config->getMappingValue('metadata_location', $class);

        return isset($metadataLocation['inner_php']);
    }

    protected function doLoadMetadata(string $class) : ClassMetadata\Model\Class_
    {
        $config = $this->config->getMappingValue('metadata_location', $class)['inner_php'];
        $method = $config['method'];

        if (isset($config['service'])) {
            $serviceInstance = $config['service'];
            $content = $serviceInstance->$method();
        } elseif (isset($config['class'])) {
            $class = $config['class'];
            $content = (new $class)->$method();
        } elseif (isset($config['static_class'])) {
            $staticClass = $config['static_class'];
            $content = $staticClass::$method();
        } else {//throw an exception
            //$content = $object->$method();
        }

        return $this->loadMetadataFromContent($content);
    }
}

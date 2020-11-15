<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\ClassMetadata;

class InnerPhpLoader extends AbstractDelegatedLoader
{
    public function supports(object $object) : bool
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
        $metadataLocation = $this->config->getMappingValue('metadata_location', $object);

        return isset($metadataLocation['inner_php']);
    }

    protected function doLoadMetadata(object $object) : ClassMetadata
    {
        $config = $this->config->getMappingValue('metadata_location', $object)['inner_php'];
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
        } else {
            $content = $object->$method();
        }

        return $this->loadMetadataFromContent($content);
    }
}

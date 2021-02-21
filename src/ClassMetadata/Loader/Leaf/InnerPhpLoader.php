<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf;

class InnerPhpLoader extends AbstractLoader
{
    protected function getRessourceType() : string
    {
        return 'method_ressource';
    }

    protected function getContentType() : string
    {
        return 'php';
    }

    protected function doLoadMetadata(string $class, string $classUsedInConfig) : array
    {
        $config = $this->config->getValueByNamespace($classUsedInConfig)['method_ressource'];
        $method = $config['method_name'];
        var_dump(__METHOD__, $config);

        $arrayContent = $classUsedInConfig::$method();
        var_dump(__METHOD__, $arrayContent);

        /*if (isset($config['service'])) {
            $serviceInstance = $config['service'];
            $arrayContent = $serviceInstance->$method();
        } elseif (isset($config['class'])) {
            $class = $config['class'];
            $arrayContent = (new $class)->$method();
        } elseif (isset($config['static_class'])) {
            $staticClass = $config['static_class'];
            $arrayContent = $staticClass::$method();
        } else {//throw an exception
            //$arrayContent = $object->$method();
        }*/

        return $arrayContent;
    }
}

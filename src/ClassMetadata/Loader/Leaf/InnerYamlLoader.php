<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf;

use Symfony\Component\Yaml\Parser;

class InnerYamlLoader extends AbstractLoader
{
    protected function getRessourceType() : string
    {
        return 'method_ressource';
    }

    protected function getContentType() : string
    {
        return 'yaml';
    }

    protected function doLoadMetadata(string $class, string $classUsedInConfig) : array
    {
        $config = $this->config->getValueByNamespace($classUsedInConfig)['method_ressource'];
        $method = $config['method_name'];

        $yamlContent = $classUsedInConfig::$method();

        /*if (isset($config['service'])) {
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
        }*/

        $arrayContent = (new Parser())->parse($yamlContent);

        return $arrayContent;
    }
}

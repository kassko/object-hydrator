<?php

namespace Kassko\ObjectHydrator;

use Symfony\Component\Config\Definition\Processor;

use function is_object;
use function get_class;
use function gettype;
use function is_callable;
use function method_exists;
use function strlen;
use function version_compare;

class ConfigurationProcessor
{
    public function processConfig(array $configs) : array
    {
        $processor = new Processor();
        $configurationDefinition = new ConfigurationDefinition();

        $validatedConfig = $processor->processConfiguration(
            $configurationDefinition,
            $configs
        );

        return $this->finalizeConfig($validatedConfig);
    }

    private function finalizeConfig(array $config) : array
    {
        if (isset($config['data_source_expressions'])) {
            $config['data_source_expressions'] = $this->completeExpressionConfig($config['data_source_expressions']);
            $config = $this->resolveServiceLocatorConfig($config);

            if (isset($config['logger_key']) && !isset($config['service_locator'])) {
                throw new \LogicException(sprintf(
                    'Cannot validate configuration.' .
                    PHP_EOL . 'As you specified a logger key, you must provide a service locator' .
                    PHP_EOL . 'to locate the logger service and resolve it.' .
                    PHP_EOL . 'The key "%s" required "%s".'
                ));
            }
        }

        return $config;
    }

    private function completeExpressionConfig(array $expressionConfig) : array
    {
        foreach ($expressionConfig['keywords'] as $key => $item) {
            $expressionConfig['keywords'][$key . '_size'] = strlen($item);
        }

        foreach ($expressionConfig['markers'] as $key => $item) {
            $expressionConfig['markers'][$key . '_size'] = strlen($item);
        }

        return $expressionConfig;
    }

    private function resolveServiceLocatorConfig(array $config) : array
    {
        $config['service_locator'] = null;
        $count = 0;

        if (isset($config['psr_container'])) {
            if (null !== $config['psr_container'] && ! $config['psr_container'] instanceof \Psr\Container\ContainerInterface) {
                throw new \LogicException(sprintf(
                    'Cannot configure path "big_hydrator.psr_container".' .
                    PHP_EOL . 'The value set to this path must be an instance of "%s". Given type "%s".',
                    \Psr\Container\ContainerInterface::class,
                    is_object($config) ? get_class($config) : gettype($config)
                ));
            }
            $config['service_locator'] = fn ($serviceKey) => ($config['psr_container'])($serviceKey);
            unset($config['psr_container']);
            $count++;

            return $config;
        }

        if (isset($config['service_provider'])) {
            if ($count) {
                throw new \LogicException(sprintf(
                    'Cannot configure path "big_hydrator.service_provider" section.' .
                    PHP_EOL . 'You must configure either "big_hydrator.psr_container" or "big_hydrator.service_provider" but not both pathes.'
                ));
            }
            if (null !== $config['service_provider'] && ! is_callable($config['service_provider'])) {
                throw new \LogicException(sprintf(
                    'Cannot configure the key "big_hydrator.service_provider".' .
                    PHP_EOL . 'The value set to this key must be a callable. Given type "%s".',
                    is_object($config) ? get_class($config) : gettype($config)
                ));
            }
            $config['service_locator'] = $config['service_provider'];
            unset($config['service_provider']);
        }

        return $config;
    }
}

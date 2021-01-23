<?php

namespace Kassko\ObjectHydrator\FrameworkBridge\Symfony\DependencyInjection;

use Kassko\ObjectHydrator\ConfigurationProcessor;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

use function class_exists;
use function sprintf;

/**
 * DoctrineExtension is an extension for the Doctrine DBAL and ORM library.
 */
class KasskoObjectHydratorExtension extends AbstractDoctrineExtension
{
    /** @var string */
    //private $defaultConnection;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration((new ConfigurationProcessor)->processConfig(), $configs);
        $hydratorConfig = new \Kassko\ObjectHydrator\Config($config);
        $hydratorConfigValues = $config->getValues();

        $container->setParameter('kassko_object_hydrator.config', $hydratorConfig);
        $container->setParameter('kassko_object_hydrator.config_class_metadata', $hydratorConfig['class_metadata']);
        $container->setParameter('kassko_object_hydrator.config_data_source_expressions', $hydratorConfig->getPartition('data_source_expressions'));
        //$container->setParameter('kassko_object_hydrator.service_locator', function ($key) {});
    }
}

<?php
namespace Kassko\FrameworkBridge\Symfony\ObjectHydrator\DependencyInjection;

use Kassko\ObjectHydrator\Config\ConfigurationProcessor;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use function class_exists;
use function sprintf;

class KasskoObjectHydratorExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = (new ConfigurationProcessor)->processConfig($configs);
        $configBag = new \Kassko\ObjectHydrator\Config\Config($config);

        $container->setParameter('kassko_object_hydrator.config', $config);
        $container->setParameter('kassko_object_hydrator.config_class_metadata', $config['class_metadata']);
        $container->setParameter('kassko_object_hydrator.config_data_source_expressions', $configBag->getPartition('data_source_expressions'));
        //$container->setParameter('kassko_object_hydrator.service_locator', function ($key) {});

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage', true)) {
            $container->removeDefinition('kassko_object_hydrator.expression_language');
        }
    }
}

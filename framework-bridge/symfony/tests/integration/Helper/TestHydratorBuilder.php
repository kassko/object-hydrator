<?php
namespace Kassko\FrameworkBridge\Symfony\ObjectHydratorIntegrationTest\Helper;

use Kassko\FrameworkBridge\Symfony\ObjectHydrator\DependencyInjection\KasskoObjectHydratorExtension;
use Kassko\ObjectHydrator\Hydrator;
use Kassko\ObjectHydratorTests\Helper\TestHydratorBuilderAbstract;
use Symfony\Component\DependencyInjection\{Container, ContainerBuilder};

class TestHydratorBuilder extends TestHydratorBuilderAbstract
{
    public function build() : Hydrator
    {
        var_dump(__METHOD__);
        return $this->createContainer()->get('Kassko\ObjectHydrator\Hydrator');
    }

    private function createContainer() : Container
    {
        $container = new ContainerBuilder();

        $extension = new KasskoObjectHydratorExtension;
        $extension->load([[]], $container);

        $container->compile();

        $this->initializeRegistry($container);

        return $container;
    }

    private function initializeRegistry($container)
    {
        $registryInitialiser = $container->get('Kassko\ObjectHydrator\RegistryInitializer');
        $registryInitialiser->initialize(
            $container->get('Kassko\ObjectHydrator\Hydrator'),
            $container->has('logger') ? $this->container->get('logger') : null
        );
    }
}

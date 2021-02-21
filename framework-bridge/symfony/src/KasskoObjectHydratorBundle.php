<?php
namespace Kassko\FrameworkBridge\Symfony\ObjectHydrator;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class KasskoObjectHydratorBundle extends Bundle
{
	/**
     * {@inheritdoc}
     */
    public function boot()
    {
        $registryInitialiser = $this->container->get('Kassko\ObjectHydrator\RegistryInitializer');
        $registryInitialiser->initialize(
            $this->container->get('Kassko\ObjectHydrator\Hydrator'),
            $this->container->has('logger') ? $this->container->get('logger') : null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        $registryInitialiser = $this->container->get('Kassko\ObjectHydrator\RegistryInitializer');
        $registryInitialiser->clear();
    }
}

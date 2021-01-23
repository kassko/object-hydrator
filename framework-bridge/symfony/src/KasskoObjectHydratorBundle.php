<?php

namespace Kassko\ObjectHydrator\FrameworkBridge\Symfony;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class KasskoObjectHydratorBundle extends Bundle
{
	/**
     * {@inheritdoc}
     */
    public function boot()
    {
        $registryInitialiser = $this->container->get('Kassko\ObjectHydrator\RegistryInitializer');
        $registryInitialiser->initialize();
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

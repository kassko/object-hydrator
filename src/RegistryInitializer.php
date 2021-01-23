<?php

namespace Kassko\ObjectHydrator;

class RegistryInitializer
{
    public function initialize(Hydrator $hydrator, ?LoggerInterface $logger) : void
    {
        Registry::getInstance()[Registry::KEY_LOGGER] = $logger ?? new \Psr\Log\NullLogger;

        $propertyLoader = new PropertyLoader($hydrator);
        Registry::getInstance()[Registry::KEY_PROPERTY_LOADER] = $propertyLoader;
    }

    public function clear()
    {
        Registry::getInstance()->clear()
    }
}

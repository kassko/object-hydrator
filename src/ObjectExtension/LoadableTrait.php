<?php

namespace Big\Hydrator\ObjectExtension;

use Big\Hydrator\Registry;

/**
 * Registry
 *
 * @author kko
 */
trait LoadableTrait
{
    public $__registered = false;

    protected function load()
    {
        if (false === $loader = $this->__getLoader()) {
            return;
        }

        $loader->load($this);
    }

    protected function loadProperty($propertyName)
    {
        if (false === $loader = $this->__getLoader()) {
            return;
        }

        $loader->loadProperty($this, $propertyName);
    }

    public function markPropertyLoaded($propertyName)
    {
        if (false === $loader = $this->__getLoader()) {
            return;
        }

        $loader->markPropertyLoaded($this, $propertyName);
    }

    public function isPropertyLoaded($propertyName)
    {
        if (false === $loader = $this->__getLoader()) {
            return;
        }

        return $loader->isPropertyLoaded($this, $propertyName);
    }

    private function __getLoader()
    {
        $registry = Registry::getInstance();
        if (! isset($registry[Registry::KEY_PROPERTY_LOADER])) {
            return false;
        }

        return $registry[Registry::KEY_PROPERTY_LOADER];
    }
}

<?php

namespace Kassko\ObjectHydrator;

/**
 * Load object properties.
 *
 * @author kko
 */
class PropertyLoader
{
    private Hydrator $hydrator;

    public function __construct(Hydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * Load an object.
     * Some properties can be loaded only when needed for performance reason.
     */
    public function load(object $object) : void
    {
        $this->hydrator->load($object);
    }

    /**
     * Load an object property.
     * This property can be loaded only if needed for performance reason.
     */
    public function loadProperty(object $object, string $propertyName) : void
    {
        $this->hydrator->loadProperty($object, $propertyName);
    }

    /**
     * Says if a property is loaded.
     */
    public function isPropertyLoaded(object $object, string $propertyName) : bool
    {
        return $this->hydrator->isPropertyLoaded($object, $propertyName);
    }

    /**
     * Mark a property as loaded.
     * If others properties are loaded by the same source, there are also mark as loaded.
     */
    public function markPropertyLoaded(object $object, string $propertyName) : void
    {
        $this->hydrator->markPropertyLoaded($object, $propertyName);
    }
}

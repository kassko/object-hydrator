<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ObjectExtension\LoadableTrait;

use function spl_object_hash;

class IdentityMap
{
    private array $identityMap = [];
    private ObjectLoadabilityChecker $objectLoadabilityChecker;

    public function __construct(ObjectLoadabilityChecker $objectLoadabilityChecker)
    {
        $this->objectLoadabilityChecker = $objectLoadabilityChecker;
    }

    public function isPropertyLoaded(object $object, string $propertyName) : bool
    {
        $objectHash = spl_object_hash($object);
        $this->fixObjectInIdentityMap($object, $objectHash);

        return isset($this->identityMap[$objectHash][$propertyName]);
    }

    public function markPropertyLoaded(object $object, string $propertyName, array $extraData = []) : void
    {
        $objectHash = spl_object_hash($object);
        $this->registerObjectToIdentityMap($object, $objectHash);
        $this->identityMap[$objectHash][$propertyName] = $extraData;
    }

    /**
     * Remove hash if it is orphan.
     * This is possible because when a object dead, it's hash is reused with another object.
     */
    private function fixObjectInIdentityMap(object $object, $objectHash) : void
    {
        $this->objectLoadabilityChecker->checkIfIsLoadable($object);

        if (false === $object->__registered) {
            unset($this->identityMap[$objectHash]);
        }
    }

    private function registerObjectToIdentityMap(object $object, $objectHash) : void
    {
        if (! isset($this->identityMap[$objectHash])) {
            $this->identityMap[$objectHash] = [];
            $this->objectLoadabilityChecker->checkIfIsLoadable($object);
            $object->__registered = true;
        }
    }
}

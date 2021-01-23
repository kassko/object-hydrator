<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ObjectExtension\LoadableTrait;

use function array_merge;
use function array_pop;
use function array_unique;
use function class_uses;
use function get_parent_class;
use function in_array;

class ObjectLoadabilityChecker
{
    public function checkIfIsLoadable(object $object) : void
    {
        if (! $this->isLoadable($object)) {
            throw new \RuntimeException(
                sprintf(
                    'To work with DataSource, the class "%s" must use the trait "%s"',
                    get_class($object),
                    LoadableTrait::class
                )
            );
        }
    }

    public function isLoadable(object $object) : bool
    {
        $traitsUses = $this->getClassUsesDeeply(get_class($object));

        return in_array(LoadableTrait::class, $traitsUses);
    }

    private function getClassUsesDeeply(string $class, bool $autoload = true) : array
    {
        /**
         * @todo Add a cache with all traits uses for each classes.
         */

        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (! empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }
}

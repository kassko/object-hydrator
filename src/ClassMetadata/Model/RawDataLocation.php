<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author kko
 */
final class RawDataLocation
{
    private string $locationName;
    private ?KeysMappingInterface $keysMapping = null;


    public function __construct(string $locationName, ?KeysMappingInterface $keysMapping = null)
    {
        $this->locationName = $locationName;
        $this->keysMapping = $keysMapping;
    }

    public function getLocationName() : string
    {
        return $this->locationName;
    }

    public function hasKeysMapping() : bool
    {
        return null !== $this->keysMapping;
    }

    public function getKeysMapping() : ?KeysMappingInterface
    {
        return $this->keysMapping;
    }

    public function setKeysMapping(KeysMappingInterface $keysMapping) : self
    {
        $this->keysMapping = $keysMapping;

        return $this;
    }
}

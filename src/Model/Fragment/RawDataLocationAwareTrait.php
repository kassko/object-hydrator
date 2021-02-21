<?php

namespace Kassko\ObjectHydrator\Model\Fragment;

use Kassko\ObjectHydrator\Model;
use Kassko\ObjectHydrator\MethodInvoker;

trait RawDataLocationAwareTrait
{
    private ?Model\RawDataLocation $rawDataLocation = null;


    public function hasRawDataLocation() : bool
    {
        return null !== $this->rawDataLocation;
    }

    public function getRawDataLocation() : ?Model\RawDataLocation
    {
        return $this->rawDataLocation;
    }

    public function setRawDataLocation(Model\RawDataLocation $rawDataLocation) : self
    {
        $this->rawDataLocation = $rawDataLocation;

        return $this;
    }

    public function locateConcernedRawData(array $rawData, MethodInvoker $methodInvoker, object $object)
    {
        if (!$this->hasRawDataLocation()) {
            return $rawData;
        }

        if ('parent' === $this->rawDataLocation->getLocationName()) {
            $concernedRawData = [];

            $keysMapping = $this->rawDataLocation->getKeysMapping();

            switch (true) {
                case $keysMapping instanceof Model\KeysMapping\Values:
                    foreach ($this->rawDataLocation->getKeysMapping()->getItems() as $keyInRawData => $keyInConcernedRawData) {
                        if (isset($rawData[$keyInRawData])) {
                            $concernedRawData[$keyInConcernedRawData] = $rawData[$keyInRawData];
                        }
                    }
                    break;

                case $keysMapping instanceof Model\KeysMapping\Prefix:
                    $keyPrefix = $keysMapping->getPrefix();
                    $keyPrefixSize = strlen($keyPrefix);

                    foreach ($rawData as $keyInRawData => $valueInRawData) {
                        if (0 === strpos($keyInRawData, $keyPrefix)) {
                            $concernedRawData[substr($keyInRawData, $keyPrefixSize)] = $valueInRawData;
                        }
                    }
                    break;

                case $keysMapping instanceof Model\KeysMapping\Method:
                    foreach ($rawData as $keyInRawData => $valueInRawData) {
                        if (null !== $keyInConcernedRawData = $methodInvoker->invokeMethod($keysMapping->getMethod(), [$keyInRawData], $object)) {
                            $concernedRawData[$keyInConcernedRawData] = $valueInRawData;
                        }
                    }
                    break;
            }
        }

        return $concernedRawData;
    }
}

<?php

namespace Kassko\ObjectHydrator\Model\KeysMapping;

use Kassko\ObjectHydrator\Model\KeysMappingInterface;

/**
 * @author kko
 */
final class Prefix implements KeysMappingInterface
{
    private string $value;


    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getPrefix() : string
    {
        return $this->value;
    }

    public function setPrefix(string $value) : self
    {
        $this->value = $value;

        return $this;
    }
}

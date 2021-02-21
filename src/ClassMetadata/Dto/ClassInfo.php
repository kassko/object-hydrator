<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Dto;

use Kassko\ObjectHydrator\Model\Fragment;

class ClassInfo
{
    use Fragment\RawDataLocationAwareTrait;

    private ?string $class = null;

    public function __construct(?string $class, ?KeysMapping $keysMapping = null)
    {
        $this->class = $class;
        $this->keysMapping = $keysMapping;
    }

    public function isObject() : bool
    {
        return null !== $this->class;
    }

    public function getClass() : ?string
    {
        return $this->class;
    }
}

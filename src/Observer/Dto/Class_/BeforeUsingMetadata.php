<?php

namespace Kassko\ObjectHydrator\Observer\Dto\Class_;

use Kassko\ObjectHydrator\Model;

final class BeforeUsingMetadata
{
    private Model\Class_ $classMetadata;


    public function from(Model\Class_ $classMetadata)
    {
        return new self($classMetadata);
    }

    public function getClassMetadata() : Model\Class_
    {
        return $this->classMetadata;
    }

    private function __construct(Model\Class_ $classMetadata)
    {
        $this->classMetadata = $classMetadata;
    }

    private function __clone() {}
}

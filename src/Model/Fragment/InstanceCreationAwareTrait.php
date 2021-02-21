<?php

namespace Kassko\ObjectHydrator\Model\Fragment;

use Kassko\ObjectHydrator\Model;
use Kassko\ObjectHydrator\MethodInvoker;

trait InstanceCreationAwareTrait
{
    private ?Model\InstanceCreation $instanceCreation = null;


    public function hasInstanceCreation() : bool
    {
        return null !== $this->instanceCreation;
    }

    public function getInstanceCreation() : ?Model\InstanceCreation
    {
        return $this->instanceCreation;
    }

    public function setInstanceCreation(Model\InstanceCreation $instanceCreation) : self
    {
        $this->instanceCreation = $instanceCreation;

        return $this;
    }
}

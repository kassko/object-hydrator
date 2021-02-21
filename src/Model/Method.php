<?php

namespace Kassko\ObjectHydrator\Model;

use Kassko\ObjectHydrator\Model\DynamicValueAbstract;

/**
 * @author kko
 */
final class Method extends DynamicValueAbstract
{
    public ?string $id = null;
    public ?string $class = null;
    public ?string $serviceKey = null;
    public ?string $name = null;
    public ?bool $static = null;
    public array $args = [];
    public bool $magicCallAllowed = false;


    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    public function isMethod() : bool
    {
        return true;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getClass() : ?string
    {
        return $this->class;
    }

    public function setClass(string $class) : self
    {
        $this->class = $class;

        return $this;
    }

    public function getServiceKey() : ?string
    {
        return $this->serviceKey;
    }

    public function setServiceKey(bool $serviceKey) : self
    {
        $this->serviceKey = $serviceKey;

        return $this;
    }

    public function isInvokerAService() : bool
    {
        return null !== $this->serviceKey;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function isStatic() : ?bool
    {
        return $this->static;
    }

    public function setStatic(bool $static) : self
    {
        $this->static = $static;

        return $this;
    }

    public function getArgs() : array
    {
        return $this->args;
    }

    public function setArgs(array $args) : self
    {
        $this->args = $args;

        return $this;
    }

    public function addArg($arg) : self
    {
        $this->args[] = $arg;

        return $this;
    }

    public function isMagicCallAllowed() : bool
    {
        return $this->magicCallAllowed;
    }

    public function setMagicCallAllowed(bool $magicCallAllowed) : self
    {
        $this->magicCallAllowed = $magicCallAllowed;

        return $this;
    }
}

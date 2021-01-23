<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Model;

use Kassko\ObjectHydrator\ClassMetadata\Model\DynamicValueAbstract;

/**
 * @author kko
 */
final class Expression extends DynamicValueAbstract
{
    public ?string $id;
    public string $value;


    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    public function isExpression() : bool
    {
        return true;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function setValue($value) : self
    {
        $this->value = $value;

        return $this;
    }
}

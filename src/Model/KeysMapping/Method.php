<?php

namespace Kassko\ObjectHydrator\Model\KeysMapping;

use Kassko\ObjectHydrator\Model\{KeysMappingInterface, Method as MethodValue};

/**
 * @author kko
 */
final class Method implements KeysMappingInterface
{
    private MethodValue $method;


    public function __construct(MethodValue $method)
    {
        $this->method = $method;
    }

    public function getMethod() : MethodValue
    {
        return $this->method;
    }

    public function setMethod(MethodValue $method) : self
    {
        $this->method = $method;

        return $this;
    }
}

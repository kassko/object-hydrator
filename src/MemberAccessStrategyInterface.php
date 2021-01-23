<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadata;

/**
* Contract for member access strategies.
*
* @author kko
*/
interface MemberAccessStrategyInterface
{
    public function getValue(ClassMetadata\Model\Property\Leaf $property);
    public function setValue($value, ClassMetadata\Model\Property\Leaf $property) : void;
}

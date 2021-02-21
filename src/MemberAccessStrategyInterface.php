<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadata;
use Kassko\ObjectHydrator\Model;

/**
* Contract for member access strategies.
*
* @author kko
*/
interface MemberAccessStrategyInterface
{
    public function getValue(Model\Property\Leaf $property);
    public function setValue($value, Model\Property\Leaf $property) : void;
}

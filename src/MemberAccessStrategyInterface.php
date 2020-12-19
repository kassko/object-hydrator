<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadata;

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

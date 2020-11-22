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
    public function getValue(ClassMetadata\Property $property);
    public function setValue($value, ClassMetadata\Property $property) : void;
}

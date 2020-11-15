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
    public function prepare(object $object, ClassMetadata $classMetadata) : void;
    public function getValue(object $object, string $fieldName);
    public function setValue($value, object $object, string $fieldName) : void;
}

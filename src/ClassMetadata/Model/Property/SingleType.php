<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Model\Property;

/**
 * @author kko
 */
final class SingleType extends Leaf
{
    public function isCollection() : bool
    {
        return false;
    }
}

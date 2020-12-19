<?php

namespace Big\Hydrator\ClassMetadata\Model\Property;

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

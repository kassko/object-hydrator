<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Model;

/**
 * @author kko
 */
abstract class DynamicValueAbstract implements DynamicValueInterface
{
    public function isExpression() : bool { return false; }
    public function isMethod() : bool { return false; }
}

<?php

namespace Kassko\ObjectHydrator\Model;

/**
 * @author kko
 */
abstract class DynamicValueAbstract implements DynamicValueInterface
{
    public function isExpression() : bool { return false; }
    public function isMethod() : bool { return false; }
}

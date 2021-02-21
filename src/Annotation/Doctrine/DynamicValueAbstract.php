<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @author kko
 */
abstract class DynamicValueAbstract implements DynamicValueInterface
{
    public function isExpression() : bool { return false; }
    public function isMethod() : bool { return false; }
}

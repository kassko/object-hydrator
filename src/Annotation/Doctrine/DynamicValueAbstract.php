<?php

namespace Big\Hydrator\Annotation\Doctrine;

/**
 * @author kko
 */
abstract class DynamicValueAbstract implements DynamicValueInterface
{
    public function isExpression() : bool { return false; }
    public function isMethod() : bool { return false; }
}

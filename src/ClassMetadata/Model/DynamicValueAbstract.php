<?php

namespace Big\Hydrator\ClassMetadata\Model;

/**
 * @author kko
 */
abstract class DynamicValueAbstract implements DynamicValueInterface
{
    public function isExpression() : bool { return false; }
    public function isMethod() : bool { return false; }
}

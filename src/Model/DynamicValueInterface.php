<?php

namespace Kassko\ObjectHydrator\Model;

/**
 * @author kko
 */
interface DynamicValueInterface
{
    public function isExpression();
    public function isMethod();
}

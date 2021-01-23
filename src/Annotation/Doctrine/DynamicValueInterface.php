<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @author kko
 */
interface DynamicValueInterface
{
    public function isExpression();
    public function isMethod();
}

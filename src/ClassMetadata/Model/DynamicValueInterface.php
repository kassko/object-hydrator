<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Model;

/**
 * @author kko
 */
interface DynamicValueInterface
{
    public function isExpression();
    public function isMethod();
}

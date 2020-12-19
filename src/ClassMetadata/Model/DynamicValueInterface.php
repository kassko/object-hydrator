<?php

namespace Big\Hydrator\ClassMetadata\Model;

/**
 * @author kko
 */
interface DynamicValueInterface
{
    public function isExpression();
    public function isMethod();
}

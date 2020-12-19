<?php

namespace Big\Hydrator\Annotation\Doctrine;

/**
 * @author kko
 */
interface DynamicValueInterface
{
    public function isExpression();
    public function isMethod();
}

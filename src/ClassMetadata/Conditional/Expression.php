<?php

namespace Big\Hydrator\ClassMetadata\Conditional;

use Big\Hydrator\ClassMetadata\Conditional;

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
class Expression extends Conditional
{
    //=== Annotations attributes (must be public) : begin ===//
    /**
     * @internal
     */
    public string $expression;
    //=== Annotations attributes : end ===//

    public function isExpression() : bool
    {
        return null !== $this->expression;
    }

    public function getExpression() : string
    {
        return $this->expression;
    }
}

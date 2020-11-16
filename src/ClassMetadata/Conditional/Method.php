<?php

namespace Big\Hydrator\ClassMetadata\Conditional;

use Big\Hydrator\ClassMetadata\Conditional;
use Big\StandardClassMetadata\Method as StdMethod;

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
class Method extends Conditional
{
    //=== Annotations attributes (must be public) : begin ===//
    /**
     * @internal
     * @var \Big\StandardClassMetadata\Method
     */
    public StdMethod $method;
    //=== Annotations attributes : end ===//

    public function isMethod() : bool
    {
        return null !== $this->method;
    }

    public function getMethod() : Method
    {
        return $this->method;
    }
}

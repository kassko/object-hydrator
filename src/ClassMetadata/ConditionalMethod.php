<?php

namespace Big\Hydrator;

use Big\StandardClassMetadata\Method;

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
class ConditionalMethod extends Conditional
{
    //=== Annotations attributes (must be public) : begin ===//
    /**
     * @internal
     * @var string
     */
    public string $id;
    /**
     * @internal
     * @var \Big\StandardClassMetadata\Method
     */
    public ?Method $method = null;
    //=== Annotations attributes : end ===//

    public function isMethod() : bool
    {
        return null !== $this->method;
    }

    public function getMethod() : ?Method
    {
        return $this->method;
    }
}

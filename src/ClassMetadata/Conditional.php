<?php

namespace Big\Hydrator;

use Big\StandardClassMetadata\Method;

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
class Conditional extends Base
{
    //=== Annotations attributes (must be public) : begin ===//
    /**
     * @internal
     * @var string
     */
    public string $id;
    /**
     * @internal
     */
    public ?string $expression = null;
    /**
     * @internal
     * @var \Big\StandardClassMetadata\Method
     */
    public ?Method $method = null;
    //=== Annotations attributes : end ===//

    public function isExpression() : bool
    {
        return null !== $this->expression;
    }

    public function getExpression() : ?string
    {
        return $this->expression;
    }

    public function isMethod() : bool
    {
        return null !== $this->method;
    }

    public function getMethod() : ?Method
    {
        return $this->method;
    }
}

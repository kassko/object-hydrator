<?php

namespace Big\Hydrator;

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
class ConditionalExpression extends Conditional
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
    //=== Annotations attributes : end ===//

    public function isExpression() : bool
    {
        return null !== $this->expression;
    }

    public function getExpression() : ?string
    {
        return $this->expression;
    }
}

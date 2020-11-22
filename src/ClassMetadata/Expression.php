<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target("ANNOTATION")
 *
 * @author kko
 */
final class Expression implements DynamicValueInterface
{
    //=== Annotations attributes (must be public) : begin ===//
    /**
     * @internal
     */
    public string $value;
    //=== Annotations attributes : end ===//

    public function getValue() : string
    {
        return $this->value;
    }
}

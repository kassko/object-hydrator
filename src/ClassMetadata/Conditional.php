<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
final class Conditional
{
    use Capability\Enabling;

    //=== Annotations attributes (must be public) : begin ===//
    /**
     * @internal
     * @var string
     */
    public string $id;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\DynamicValueInterface
     */
    public DynamicValueInterface $value;
    //=== Annotations attributes : end ===//

    public function getId() : string
    {
        return $this->id;
    }

    public function getValue() : DynamicValueInterface
    {
        return $this->value;
    }
}

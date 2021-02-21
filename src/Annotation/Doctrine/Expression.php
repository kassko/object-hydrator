<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target("ANNOTATION")
 *
 * @author kko
 */
final class Expression extends DynamicValueAbstract
{
    use Capability\ToArrayConvertible;

    /**
     * @internal
     * @var string
     */
    public string $id;
    /**
     * @internal
     */
    public string $value;
}

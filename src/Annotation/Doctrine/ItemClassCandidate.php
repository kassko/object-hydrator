<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target("ANNOTATION")
 *
 * @author kko
 */
final class ItemClassCandidate
{
    use Capability\Enabling;
    use Capability\ToArrayConvertible;

    /**
     * @var string
     */
    public string $class;
    /**
     * @internal
     */
    public ?ValueObject $valueObject = null;
    /**
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Expression
     */
    public Expression $discriminatorExpression;
    /**
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Method
     */
    public Method $discriminatorMethod;
    /**
     * @var string
     */
    public string $discriminatorRef;
}

<?php

namespace Big\Hydrator\Annotation\Doctrine;

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
     * @var \Big\Hydrator\Annotation\Doctrine\Expression
     */
    public Expression $discriminatorExpression;
    /**
     * @var \Big\Hydrator\Annotation\Doctrine\Method
     */
    public Method $discriminatorMethod;
    /**
     * @var string
     */
    public string $discriminatorRef;
}

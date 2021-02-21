<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target("ANNOTATION")
 *
 * @author kko
 */
final class CallbacksAssigningHydratedValue
{
    use Capability\Enabling;
    use Capability\ToArrayConvertible;

    /**
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Methods
     */
    public ?Methods $before = null;
    /**
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Methods
     */
    public ?Methods $after = null;
}

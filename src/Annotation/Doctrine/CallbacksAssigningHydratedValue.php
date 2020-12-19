<?php

namespace Big\Hydrator\Annotation\Doctrine;

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
     * @var \Big\Hydrator\Annotation\Doctrine\Methods
     */
    public ?Methods $before = null;
    /**
     * @var \Big\Hydrator\Annotation\Doctrine\Methods
     */
    public ?Methods $after = null;
}

<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target("ANNOTATION")
 *
 * @author kko
 */
final class Callbacks
{
    use Capability\Enabling;
    use Capability\ToArrayConvertible;

    /**
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Method
     */
    public ?Method $before = null;
    /**
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Method
     */
    public ?Method $after = null;
    /**
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Method
     */
    public ?Methods $beforeCollection = null;
    /**
     * @var \Kassko\ObjectHydrator\Annotation\Doctrine\Method
     */
    public ?Methods $afterCollection = null;
}

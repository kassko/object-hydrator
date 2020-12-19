<?php

namespace Big\Hydrator\Annotation\Doctrine;

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
     * @var \Big\Hydrator\Annotation\Doctrine\Method
     */
    public ?Method $before = null;
    /**
     * @var \Big\Hydrator\Annotation\Doctrine\Method
     */
    public ?Method $after = null;
    /**
     * @var \Big\Hydrator\Annotation\Doctrine\Method
     */
    public ?Methods $beforeCollection = null;
    /**
     * @var \Big\Hydrator\Annotation\Doctrine\Method
     */
    public ?Methods $afterCollection = null;
}

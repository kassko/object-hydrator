<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target("ANNOTATION")
 *
 * @author kko
 */
final class ItemClassCandidates
{
    use Capability\Enabling;
    use Capability\ToArrayConvertible;

    /**
     * One or more ItemClassCandidate annotations.
     *
     * @var array<\Kassko\ObjectHydrator\Annotation\Doctrine\ItemClassCandidate>
     */
    public $items = [];
}

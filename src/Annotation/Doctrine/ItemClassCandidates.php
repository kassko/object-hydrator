<?php

namespace Big\Hydrator\Annotation\Doctrine;

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
     * @var array<\Big\Hydrator\Annotation\Doctrine\ItemClassCandidate>
     */
    public $items = [];
}

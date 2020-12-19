<?php

namespace Big\Hydrator\Annotation\Doctrine;

/**
* @Annotation
* @Target({"CLASS", "ANNOTATION"})
*
* @author kko
*/
final class Methods
{
	use Capability\ToArrayConvertible;

    /**
     * One or more Method annotations.
     *
     * @var array<\Big\Hydrator\Annotation\Doctrine\Method>
     */
    public $items = [];
}

<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

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
     * @var array<\Kassko\ObjectHydrator\Annotation\Doctrine\Method>
     */
    public $items = [];
}

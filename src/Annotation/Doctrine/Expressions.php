<?php

namespace Big\Hydrator\Annotation\Doctrine;

/**
* @Annotation
* @Target("CLASS")
*
* @author kko
*/
final class Expressions
{
	use Capability\ToArrayConvertible;

    /**
     * One or more Method annotations.
     *
     * @var array<\Big\Hydrator\Annotation\Doctrine\Expression>
     */
    public $items = [];
}

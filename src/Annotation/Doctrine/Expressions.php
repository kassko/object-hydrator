<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

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
     * @var array<\Kassko\ObjectHydrator\Annotation\Doctrine\Expression>
     */
    public $items = [];
}

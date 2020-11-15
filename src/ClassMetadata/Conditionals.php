<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author kko
 */
class Conditionals extends Base
{
	//=== Annotations attributes (must be public) : begin ===//
    /**
     * One or more Conditional annotations.
     *
     * @var array<\Big\Hydrator\ClassMetadata\Conditional>
     */
    public $items = [];
    //=== Annotations attributes : end ===//
}

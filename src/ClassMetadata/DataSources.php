<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author kko
 */
class DataSources extends Base
{
	//=== Annotations attributes (must be public) : begin ===//
    /**
     * One or more DataSource annotations.
     *
     * @var array<\Big\Hydrator\ClassMetadata\DataSource>
     */
    public $items = [];
    //=== Annotations attributes : end ===//
}

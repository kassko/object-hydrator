<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author kko
 */
final class DataSources
{
    use Capability\Enabling;

    //=== Annotations attributes (must be public) : begin ===//
    /**
     * One or more DataSource annotations.
     *
     * @var array<\Big\Hydrator\ClassMetadata\DataSource>
     */
    public $items = [];
    //=== Annotations attributes : end ===//
}

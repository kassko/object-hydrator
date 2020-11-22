<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author kko
 */
final class ClassOptions
{
    use Capability\Enabling;

    public $defaultHydrateAllProperties = true;
}

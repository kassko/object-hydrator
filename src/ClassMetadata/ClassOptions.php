<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author kko
 */
class ClassOptions extends Base
{
    public $propertiesExcludedByDefault = false;
}

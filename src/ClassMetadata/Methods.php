<?php

namespace Big\Hydrator\ClassMetadata;

/**
* @Annotation
* @Target("ANNOTATION")
*
* @author kko
*/
final class Methods
{
    /**
     * One or more Method annotations.
     *
     * @var array<\Big\StandardClassMetadata\Method>
     */
    public $items = [];
}

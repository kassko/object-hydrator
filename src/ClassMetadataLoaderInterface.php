<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadata;

interface ClassMetadataLoaderInterface
{
    public function loadMetadata(object $object) : array;
}

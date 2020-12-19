<?php

namespace Big\Hydrator\ClassMetadataLoader;

use Big\Hydrator\ClassMetadata;

abstract class AbstractPhpArrayContentLoader extends AbstractLoader
{
    protected function loadMetadataFromContent(array $content) : ClassMetadata\Model\Class_;
    {
        return new ClassMetadata;
    }
}

<?php

namespace Kassko\ObjectHydrator\ClassMetadataLoader;

use Kassko\ObjectHydrator\ClassMetadata;

abstract class AbstractPhpArrayContentLoader extends AbstractLoader
{
    protected function loadMetadataFromContent(array $content) : ClassMetadata\Model\Class_;
    {
        return new ClassMetadata;
    }
}

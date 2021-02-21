<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\Model;

interface ModelLoaderInterface
{
    public function load(string $class, ?string $classUsedInConfig = null) : Model\Class_;
}

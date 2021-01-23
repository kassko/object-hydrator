<?php

namespace Kassko\ObjectHydrator;

interface ClassMetadataDelegatedLoaderInterface
{
    public function supports(string $class) : bool;
}

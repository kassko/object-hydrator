<?php

namespace Big\Hydrator;

interface ClassMetadataDelegatedLoaderInterface
{
    public function supports(object $object) : bool;
}

<?php

namespace Big\Hydrator;

trait PropertyCandidatesResolverAwareTrait
{
    private $propertyCandidatesResolver;

    public function setPropertyCandidatesResolver(PropertyCandidatesResolver $propertyCandidatesResolver)
    {
        $this->propertyCandidatesResolver = $propertyCandidatesResolver;

        return $this;
    }
}

<?php

namespace Big\Hydrator;

trait CandidatePropertiesResolverAwareTrait
{
    private $candidatePropertiesResolver;

    public function setCandidatePropertiesResolver(CandidatePropertiesResolver $candidatePropertiesResolver)
    {
        $this->candidatePropertiesResolver = $candidatePropertiesResolver;

        return $this;
    }

    public function resolveManagedProperty(string $propertyName, $classMetadata)
    {
        return $this->candidatePropertiesResolver->resolveVersionToUse(
            $classMetadata->getCandidateProperties($propertyName)
        );
    }
}

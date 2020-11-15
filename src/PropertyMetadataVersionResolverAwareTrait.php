<?php

namespace Big\Hydrator;

trait PropertyMetadataVersionResolverAwareTrait
{
    private $propertyMetadataVersionResolver;

    public function setPropertyMetadataVersionresolver(PropertyMetadataVersionResolver $propertyMetadataVersionResolver)
    {
        $this->propertyMetadataVersionResolver = $propertyMetadataVersionResolver;
    }

    public function resolveManagedProperty(string $propertyName, $classMetadata)
    {
        return $this->propertyMetadataVersionResolver->resolveVersionToUse(
            $classMetadata->getManagedPropertyVersions($propertyName)
        );
    }
}

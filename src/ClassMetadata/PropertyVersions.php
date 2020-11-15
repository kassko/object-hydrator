<?php

namespace Big\Hydrator\ClassMetadata;

class PropertyVersions
{
    private string $name;
    /**
     * @var Property[]
     */
    private array $versions;

    public function __construct(string $name, array $versions)
    {
        $this->name = $name;
        $this->versions = $versions;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getVersions() : array
    {
        return $this->versions;
    }
}

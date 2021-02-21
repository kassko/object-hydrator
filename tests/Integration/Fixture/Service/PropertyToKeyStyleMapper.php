<?php
namespace Kassko\ObjectHydratorIntegrationTest\Fixture\Service;

class PropertyToKeyStyleMapper
{
    public function mapPropertyNameToKey(string $propertyName)
    {
        return '_' . $propertyName . '_';
    }
}

<?php
namespace Kassko\ObjectHydratorTest\Integration\Fixture\Service;

class PropertyToKeyStyleMapper
{
    public function mapPropertyNameToKey(string $propertyName)
    {
        return '_' . $propertyName . '_';
    }
}

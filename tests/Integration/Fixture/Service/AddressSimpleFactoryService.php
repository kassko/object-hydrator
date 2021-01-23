<?php
namespace Kassko\ObjectHydratorTest\Integration\Fixture\Service;

use Kassko\ObjectHydratorTest\Integration\Fixture\Model\Address\AddressSimple;

class AddressSimpleFactoryService
{
    public function create()
    {
        return new AddressSimple;
    }
}

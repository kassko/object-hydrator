<?php
namespace Kassko\ObjectHydratorIntegrationTest\Fixture\Service;

use Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Address\AddressSimple;

class AddressSimpleFactoryService
{
    public function create()
    {
        return new AddressSimple;
    }
}

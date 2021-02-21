<?php

namespace Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Address;

use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

final class AddressSimple
{
    private ?string $street = null;
    private ?string $city = null;
    private ?string $country = null;


    public function getStreet() : ?string
    {
        return $this->street;
    }

    public function getCity() : ?string
    {
        return $this->city;
    }

    public function getCountry() : ?string
    {
        return $this->country;
    }
}

<?php

namespace Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Address;

use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

final class AddressWithFactoryMethod
{
    private ?string $street = null;
    private ?string $city = null;
    private ?string $country = null;


    public static function from(?string $street = null, ?string $city = null) : self
    {
        return new self($street, $city);
    }

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

    private function __construct(?string $street = null, ?string $city = null)
    {
        $this->street = $street;
        $this->city = $city;
    }
}

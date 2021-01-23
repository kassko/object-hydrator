<?php

namespace Kassko\ObjectHydratorTest\Integration\Fixture\Model\Address;

use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

final class AddressWithDynamicAttributes
{
    /**
     * @BHY\PropertyConfig\SingleType(_keyInRawData=@BHY\Expression("variables('street')"))
     */
    private ?string $street = null;
    /**
     * @BHY\PropertyConfig\SingleType(_keyInRawData=@BHY\Expression("variables('city')"))
     */
    private ?string $city = null;
    /**
     * @BHY\PropertyConfig\SingleType(_keyInRawData=@BHY\Expression("variables('country')"))
     */
    private ?string $country = null;


    public function __construct(?string $street = null, ?string $city = null, ?string $country = null)
    {
        $this->street = $street;
        $this->city = $city;
        $this->country = $country;
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

}

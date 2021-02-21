<?php

namespace Kassko\ObjectHydratorIntegrationTest;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Address\AddressSimple;
use Kassko\ObjectHydratorIntegrationTest\Helper;
use PHPUnit\Framework\TestCase;

class _021_HydrateProperty_RawDataLocation_Method_Test extends TestCase
{
    use Helper\IntegrationTestTrait;

    public function setup() : void
    {
        $this->initHydrator();
    }

    /**
     * @test
     */
    public function letsGo()
    {
        $rawData = [
            'first_name' => 'Dany',
            'last_name' => 'Gomes',
            'billing_street' => '01 Lloyd Road',
            'billing_city' => 'South Siennaborough',
            'billing_country' => 'Tonga',
            'delivery_street' => '12 Lloyd Road',
            'delivery_city' => 'North Siennaborough',
            'delivery_country' => 'Tonga',
        ];

        $person = new class(1) {
            private int $id;
            private ?string $firstName = null;
            private ?string $lastName = null;
            /**
             * @BHY\PropertyConfig\SingleType(
             *      class="\Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Address\AddressSimple",
             *      rawDataLocation=@BHY\RawDataLocation(
             *          locationName="parent",
             *          keysMappingMethod=@BHY\Method(name="mapBillingAddressKey")
             *      )
             * )
             */
            private ?AddressSimple $billingAddress = null;
            /**
             * @BHY\PropertyConfig\SingleType(
             *      class="\Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Address\AddressSimple",
             *      rawDataLocation=@BHY\RawDataLocation(
             *          locationName="parent",
             *          keysMappingMethod=@BHY\Method(name="mapDeliveryAddressKey")
             *      )
             * )
             */
            private ?AddressSimple $deliveryAddress = null;

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getFirstName() : ?string { return $this->firstName; }
            public function setFirstName(string $firstName) { $this->firstName = $firstName; }

            public function getLastName() : ?string { return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }

            public function getBillingAddress() : ?AddressSimple { return $this->billingAddress; }
            public function setBillingAddress(AddressSimple $billingAddress) { $this->billingAddress = $billingAddress; }

            public function getDeliveryAddress() : ?AddressSimple { return $this->deliveryAddress; }
            public function setDeliveryAddress(AddressSimple $deliveryAddress) { $this->deliveryAddress = $deliveryAddress; }

            public function mapBillingAddressKey(string $key) {
                return false !== strpos($key, 'billing_') ? substr($key, strlen('billing_')) : null;
            }

            public function mapDeliveryAddressKey(string $key) {
                return false !== strpos($key, 'delivery_') ? substr($key, strlen('delivery_')) : null;
            }
        };


        $this->hydrator->hydrate($person, $rawData);

        $this->assertSame(1, $person->getId());
        $this->assertSame('Dany', $person->getFirstName());
        $this->assertSame('Gomes', $person->getLastName());

        $this->assertSame('01 Lloyd Road', $person->getBillingAddress()->getStreet());
        $this->assertSame('South Siennaborough', $person->getBillingAddress()->getCity());
        $this->assertSame('Tonga', $person->getBillingAddress()->getCountry());

        $this->assertSame('12 Lloyd Road', $person->getDeliveryAddress()->getStreet());
        $this->assertSame('North Siennaborough', $person->getDeliveryAddress()->getCity());
        $this->assertSame('Tonga', $person->getDeliveryAddress()->getCountry());
    }
}

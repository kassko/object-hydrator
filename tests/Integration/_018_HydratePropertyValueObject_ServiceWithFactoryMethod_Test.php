<?php

namespace Kassko\ObjectHydratorIntegrationTest;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Address;
use Kassko\ObjectHydratorIntegrationTest\Helper;
use PHPUnit\Framework\TestCase;

class _018_HydratePropertyValueObject_ServiceWithFactoryMethod_Test extends TestCase
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
            'billing_address' => [
                'street' => '01 Lloyd Road',
                'city' => 'South Siennaborough',
                'country' => 'Tonga',
            ],
            'delivery_address' => [
                'street' => '12 Lloyd Road',
                'city' => 'North Siennaborough',
                'country' => 'Tonga',
            ],
        ];


        $person = new class(1) {
            private int $id;
            private ?string $firstName = null;
            private ?string $lastName = null;
            /**
             * @BHY\PropertyConfig\SingleType(
             *      class="\Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Address\AddressSimple",
             *      instanceCreation=@BHY\InstanceCreation(
             *          factoryMethod=@BHY\Method(
             *              class="Kassko\ObjectHydratorIntegrationTest\Fixture\Service\AddressSimpleFactoryService",
             *              name="create"
             *          ),
             *          setPropertiesThroughCreationMethodWhenPossible=true
             *      )
             * )
             */
            private ?Address\AddressSimple $billingAddress = null;
            /**
             * @BHY\PropertyConfig\SingleType(
             *      class="\Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Address\AddressWithFactoryMethod",
             *      instanceCreation=@BHY\InstanceCreation(
             *          factoryMethodName="from",
             *          setPropertiesThroughCreationMethodWhenPossible=true
             *      )
             * )
             */
            private ?Address\AddressWithFactoryMethod $deliveryAddress = null;

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getFirstName() : ?string { return $this->firstName; }
            public function setFirstName(string $firstName) { $this->firstName = $firstName; }

            public function getLastName() : ?string { return $this->lastName; }
            public function setLastName(string $lastName) { $this->lastName = $lastName; }

            public function getBillingAddress() : ?Address\AddressSimple { return $this->billingAddress; }
            public function setBillingAddress(Address\AddressSimple $billingAddress) { $this->billingAddress = $billingAddress; }

            public function getDeliveryAddress() : ?Address\AddressWithFactoryMethod { return $this->deliveryAddress; }
            public function setDeliveryAddress(Address\AddressWithFactoryMethod $deliveryAddress) { $this->deliveryAddress = $deliveryAddress; }
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

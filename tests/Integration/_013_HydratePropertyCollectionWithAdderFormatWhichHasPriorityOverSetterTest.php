<?php

namespace Kassko\ObjectHydratorIntegrationTest;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydratorIntegrationTest\Fixture;
use Kassko\ObjectHydratorIntegrationTest\Helper;
use PHPUnit\Framework\TestCase;

class _013_HydratePropertyCollectionWithAdderFormatWhichHasPriorityOverSetterTest extends TestCase
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
        $primaryData = [
            'passengers' => [
                [
                    'id' => 1,
                    'first_name' => 'Dany',
                    'last_name' => 'Gomes',
                ],
                [
                    'id' => 2,
                    'first_name' => 'Bogdan',
                    'last_name' => 'Vassilescu',
                ],
            ]
        ];

        /**
         * @BHY\ClassConfig(defaultAdderNameFormat="append%sItem")
         */
        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\PropertyConfig\CollectionType(itemsClass="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Flight\Passenger")
             */
            private array $passengers = [];
            private array $stops = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function setPassengers(array $passengers) {
                /*$this->passengers = $passengers;*/
                throw new \Exception(
                    'Adder "appendPassengersItem" built from default adder format must take priority over setter "setPassengers"' .
                    PHP_EOL . 'but the opposite occured.'
                );
            }
            public function appendPassengersItem(Fixture\Model\Flight\Passenger $passenger) { $this->passengers[] = $passenger; }
        };


        $this->hydrator->hydrate($flight, $primaryData);

        $this->assertNotNull($flight->getPassengers());
        $this->assertCount(2, $flight->getPassengers());

        foreach ($flight->getPassengers() as $passengerKey => $passenger) {
            $rawPassenger = $primaryData['passengers'][$passengerKey];

            $this->assertInstanceOf(Fixture\Model\Flight\Passenger::class, $passenger);
            $this->assertSame($rawPassenger['id'], $passenger->getId());
            $this->assertSame($rawPassenger['first_name'], $passenger->getFirstName());
            $this->assertSame($rawPassenger['last_name'], $passenger->getLastName());
        }
    }
}

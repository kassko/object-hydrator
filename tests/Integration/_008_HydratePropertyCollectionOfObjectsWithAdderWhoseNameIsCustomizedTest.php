<?php

namespace Big\HydratorTest\Integration;

use Big\Hydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Big\HydratorTest\Integration\Fixture;
use PHPUnit\Framework\TestCase;

class _008_HydratePropertyCollectionOfObjectsWithAdderWhoseNameIsCustomizedTest extends TestCase
{
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
            ],
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\PropertyConfig\CollectionType(itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\Passenger", adder="addPassenger")
             */
            private array $passengers = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function addPassenger(Fixture\Model\Flight\Passenger $passenger) { $this->passengers[] = $passenger; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

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

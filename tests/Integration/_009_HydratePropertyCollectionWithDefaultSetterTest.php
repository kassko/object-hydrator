<?php

namespace Kassko\ObjectHydratorTest\Integration;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydratorTest\Integration\Fixture;
use PHPUnit\Framework\TestCase;

class _009_HydratePropertyCollectionWithDefaultSetterTest extends TestCase
{
    /**
     * @test
     */
    public function letsGo()
    {
        $primaryData = [
            'passengers' => [//collection of objects
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
            'stops' => [//collection of scalars
                'London',
                'Paris',
                'Vienna',
                'Bucharest'
            ],
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\PropertyConfig\CollectionType(itemsClass="Kassko\ObjectHydratorTest\Integration\Fixture\Model\Flight\Passenger")
             */
            private array $passengers = [];
            /**
             * @BHY\PropertyConfig\CollectionType
             */
            private array $stops = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function setPassengers(array $passengers) { $this->passengers = $passengers; }

            public function getStops() : array { return $this->stops; }
            public function setStops(array $stops) { $this->stops = $stops; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

        $this->assertNotNull($flight->getPassengers());
        $this->assertCount(2, $flight->getPassengers());
        foreach ($flight->getPassengers() as $passengerKey => $passenger) {
            $rawPassenger = $primaryData['passengers'][$passengerKey];

            $this->assertInstanceOf(Fixture\Model\Flight\Passenger::class, $passenger);
            $this->assertSame($rawPassenger['id'], $passenger->getId());
            $this->assertSame($rawPassenger['first_name'], $passenger->getFirstName());
            $this->assertSame($rawPassenger['last_name'], $passenger->getLastName());
        }

        $this->assertNotNull($flight->getStops());
        $this->assertCount(4, $flight->getStops());
        foreach ($flight->getStops() as $stopKey => $stop) {
            $this->assertSame($primaryData['stops'][$stopKey], $stop);
        }
    }
}

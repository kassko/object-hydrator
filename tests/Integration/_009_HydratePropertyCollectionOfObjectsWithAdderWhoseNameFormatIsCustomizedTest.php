<?php

namespace Big\HydratorTest\Integration;

use Big\Hydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Big\HydratorTest\Integration\Fixture;
use PHPUnit\Framework\TestCase;

class _009_HydratePropertyCollectionOfObjectsWithAdderWhoseNameFormatIsCustomizedTest extends TestCase
{
    /**
     * @test
     *
     * @BHY\ClassConfig(defaultAdderNameFormat="append%sItem")
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
            'stops' => [
                'London',
                'Paris',
                'Vienna',
                'Bucharest'
            ],
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\PropertyConfig\CollectionType(itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\Passenger")
             */
            private array $passengers = [];
            private array $stops = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function appendPassengersItem(Fixture\Model\Flight\Passenger $passenger) { $this->passengers[] = $passenger; }

            public function getStops() : array { return $this->stops; }
            public function appendStopItem(string $stop) { $this->stops[] = $stop; }
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

        foreach ($flight->getStops() as $stopKey => $stop) {
            $this->assertSame($primaryData['stops'][$stopKey], $stop);
        }
    }
}

<?php

namespace Big\HydratorTest\Integration;

use Big\Hydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Big\HydratorTest\Integration\Fixture;
use PHPUnit\Framework\TestCase;

class __013_UseConfigCandidatesTest extends TestCase
{
    /**
     * test
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
                    'prenume' => 'Bogdan',
                    'nume' => 'Vassilescu',
                ],
            ],
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\PropertyConfig\Candidates({
             *      @BHY\PropertyConfig\CollectionType(
             *          discriminatorExpression=@BHY\Expression("rawDataKeyExists('passengers.last_name')"),
             *          itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\PassengerWithConfigCandidatesToResolveAtRuntime"
             *      ),
             *      @BHY\PropertyConfig\CollectionType(
             *          discriminatorExpression=@BHY\Expression("rawDataKeyExists('passengers.nume')"),
             *          itemsClass="Big\HydratorTest\Integration\Fixture\Model\Flight\PassengerWithConfigCandidatesToResolveAtRuntime"
             *      )
             * })
             */
            private array $passengers = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function addPassengersItem(Fixture\Model\Flight\PassengerWithConfigCandidatesToResolveAtRuntime $passenger)
            {
                $this->passengers[] = $passenger;
            }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

        $passengers = $flight->getPassengers();

        $passenger = $passengers[0];
        $passengerRawData = $primaryData['passengers'][0];
        $this->assertInstanceOf(Fixture\Model\Flight\PassengerWithConfigCandidatesToResolveAtRuntime::class, $passenger);
        $this->assertSame($passengerRawData['id'], $passenger->getId());
        $this->assertSame($passengerRawData['first_name'], $passenger->getFirstName());
        $this->assertSame($passengerRawData['last_name'], $passenger->getLastName());

        $passenger = $passengers[1];
        $passengerRawData = $primaryData['passengers'][1];
        $this->assertInstanceOf(Fixture\Model\Flight\PassengerWithConfigCandidatesToResolveAtRuntime::class, $passenger);
        $this->assertSame($passengerRawData['id'], $passenger->getId());
        $this->assertSame($passengerRawData['prenume'], $passenger->getFirstName());
        $this->assertSame($passengerRawData['nume'], $passenger->getLastName());
    }
}

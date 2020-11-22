<?php

namespace Big\HydratorTest\Integration;

use Big\Hydrator\{ClassMetadata as BHY, HydratorBuilder};
use PHPUnit\Framework\TestCase;

class _6_HydrateCollectionOfObjectsTest extends TestCase
{
    /**
     * @test
     */
    public function hydrateCollectionOfObjectsWithSetter()
    {
        $primaryData = [
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
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\Property(collection=true, class="Big\HydratorTest\Integration\Passenger")
             * @var array
             */
            private array $passengers = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function setPassengers(array $passengers) { $this->passengers = $passengers; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

        foreach ($flight->getPassengers() as $passengerKey => $passenger) {
            $this->assertInstanceOf(Passenger::class, $passenger);
            $this->assertSame($primaryData[$passengerKey]['id'], $passenger->getId());
            $this->assertSame($primaryData[$passengerKey]['first_name'], $passenger->getFirstName());
            $this->assertSame($primaryData[$passengerKey]['last_name'], $passenger->getLastName());
        }
    }

    /**
     * @test
     */
    public function hydrateCollectionOfObjectsWithAdderDefaultName()
    {
        $primaryData = [
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
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\Property(collection=true, class="Big\HydratorTest\Integration\Passenger")
             * @var array
             */
            private array $passengers = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function addPassengersItem(Passenger $passenger) { $this->passengers[] = $passengers; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

        foreach ($flight->getPassengers() as $passengerKey => $passenger) {
            $this->assertInstanceOf(Passenger::class, $passenger);
            $this->assertSame($primaryData[$passengerKey]['id'], $passenger->getId());
            $this->assertSame($primaryData[$passengerKey]['first_name'], $passenger->getFirstName());
            $this->assertSame($primaryData[$passengerKey]['last_name'], $passenger->getLastName());
        }
    }

    /**
     * @test
     */
    public function hydrateCollectionOfObjectsWithAdderWhoseNameIsCustomized()
    {
        $primaryData = [
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
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\Property(collection=true, class="Big\HydratorTest\Integration\Passenger", adder="addPassenger")
             * @var array
             */
            private array $passengers = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function addPassenger(Passenger $passenger) { $this->passengers[] = $passenger; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

        foreach ($flight->getPassengers() as $passengerKey => $passenger) {
            $this->assertInstanceOf(Passenger::class, $passenger);
            $this->assertSame($primaryData[$passengerKey]['id'], $passenger->getId());
            $this->assertSame($primaryData[$passengerKey]['first_name'], $passenger->getFirstName());
            $this->assertSame($primaryData[$passengerKey]['last_name'], $passenger->getLastName());
        }
    }

    /**
     * @test
     *
     * @BHY\ClassOptions(defaultAdderNameFormat="append%sItem")
     */
    public function hydrateCollectionOfObjectsWithAdderWhoseNameFormatIsCustomized()
    {
        $primaryData = [
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
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\Property(collection=true, class="Big\HydratorTest\Integration\Passenger")
             * @var array
             */
            private array $passengers = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function addPassenger(Passenger $passenger) { $this->passengers[] = $passenger; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

        foreach ($flight->getPassengers() as $passengerKey => $passenger) {
            $this->assertInstanceOf(Passenger::class, $passenger);
            $this->assertSame($primaryData[$passengerKey]['id'], $passenger->getId());
            $this->assertSame($primaryData[$passengerKey]['first_name'], $passenger->getFirstName());
            $this->assertSame($primaryData[$passengerKey]['last_name'], $passenger->getLastName());
        }
    }

    /**
     * @test
     *
     * @BHY\ClassOptions(defaultAdderNameFormat="append%sItem")
     */
    public function hydrateCollectionOfObjectsWithAdderFormatAndSetterBothCandidates_AdderFormatHasPriority()
    {
        $primaryData = [
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
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\Property(collection=true, class="Big\HydratorTest\Integration\Passenger")
             * @var array
             */
            private array $passengers = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function setPassengers(array $passengers) { $this->passengers = $passengers; }
            public function appendPassengersItem(Passenger $passenger) { $this->passengers[] = $passenger; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

        foreach ($flight->getPassengers() as $passengerKey => $passenger) {
            $this->assertInstanceOf(Passenger::class, $passenger);
            $this->assertSame($primaryData[$passengerKey]['id'], $passenger->getId());
            $this->assertSame($primaryData[$passengerKey]['first_name'], $passenger->getFirstName());
            $this->assertSame($primaryData[$passengerKey]['last_name'], $passenger->getLastName());
        }
    }

    /**
     * @test
     *
     * @BHY\ClassOptions(defaultAdderNameFormat="append%sItem")
     */
    public function hydrateCollectionOfObjectsWithAdderAndAdderFormatBothCandidates_AdderHasPriority()
    {
        $primaryData = [
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
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\Property(collection=true, class="Big\HydratorTest\Integration\Passenger", adder="addPassenger")
             * @var array
             */
            private array $passengers = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function appendPassenger(Passenger $passenger) { $this->passengers[] = $passengers; }
            public function addPassenger(Passenger $passenger) { $this->passengers[] = $passenger; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

        foreach ($flight->getPassengers() as $passengerKey => $passenger) {
            $this->assertInstanceOf(Passenger::class, $passenger);
            $this->assertSame($primaryData[$passengerKey]['id'], $passenger->getId());
            $this->assertSame($primaryData[$passengerKey]['first_name'], $passenger->getFirstName());
            $this->assertSame($primaryData[$passengerKey]['last_name'], $passenger->getLastName());
        }
    }

    /**
     * @test
     */
    public function hydrateCollectionOfObjectsWithIndexAdder()
    {
        $primaryData = [
            '1-Dany-Gomes' => [
                'id' => 1,
                'first_name' => 'Dany',
                'last_name' => 'Gomes',
            ],
            '2-Bogdan-Vassilescu' => [
                'id' => 2,
                'first_name' => 'Bogdan',
                'last_name' => 'Vassilescu',
            ],
        ];

        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\Property(collection=true, class="Big\HydratorTest\Integration\Passenger")
             * @var array
             */
            private array $passengers = [];

            public function __construct(string $id) { $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : array { return $this->passengers; }
            public function addPassengersItem(string $key, Passenger $passenger) { $this->passengers[$key] = $passenger; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($flight, $primaryData);

        foreach ($flight->getPassengers() as $passengerKey => $passenger) {
            $this->assertInstanceOf(Passenger::class, $passenger);
            $this->assertSame($primaryData[$passengerKey]['id'], $passenger->getId());
            $this->assertSame($primaryData[$passengerKey]['first_name'], $passenger->getFirstName());
            $this->assertSame($primaryData[$passengerKey]['last_name'], $passenger->getLastName());
        }
    }
}

class Passenger
{
    private ?int $id = null;
    /**
     * @BHY\Property(keyInRawData="first_name")
     */
    private ?string $firstName = null;
    /**
     * @BHY\Property(keyInRawData="last_name")
     */
    private ?string $lastName = null;

    public function __construct(?int $id = null) { $this->id = $id; }

    public function getId() : int { return $this->id; }

    public function getFirstName() : ?string { return $this->firstName; }
    public function setFirstName(string $firstName) { $this->firstName = $firstName; }

    public function getLastName() : ?string { return $this->lastName; }
    public function setLastName(string $lastName) { $this->lastName = $lastName; }
}

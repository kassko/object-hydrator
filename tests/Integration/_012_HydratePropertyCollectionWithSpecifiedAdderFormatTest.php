<?php

namespace Kassko\ObjectHydratorIntegrationTest;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydratorIntegrationTest\Fixture;
use Kassko\ObjectHydratorIntegrationTest\Helper;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class _012_HydratePropertyCollectionWithSpecifiedAdderFormatTest extends TestCase
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

        /**
         * @BHY\ClassConfig(defaultAdderNameFormat="append%sItem")
         */
        $flight = new class('W6047') {
            private string $id;
            /**
             * @BHY\PropertyConfig\CollectionType(itemsClass="Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Flight\Passenger")
             */
            private ArrayCollection $passengers;
            /**
             * @BHY\PropertyConfig\CollectionType
             */
            private array $stops = [];

            public function __construct(string $id) { $this->passengers = new ArrayCollection; $this->id = $id; }

            public function getId() : string { return $this->id; }

            public function getPassengers() : ArrayCollection { return $this->passengers; }
            public function appendPassengersItem(Fixture\Model\Flight\Passenger $passenger) {
                $this->passengers->add($passenger);
            }

            public function getStops() : array { return $this->stops; }
            public function appendStopsItem(string $stop) { $this->stops[] = $stop; }
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

        $this->assertNotNull($flight->getStops());
        $this->assertCount(4, $flight->getStops());

        foreach ($flight->getStops() as $stopKey => $stop) {
            $this->assertSame($primaryData['stops'][$stopKey], $stop);
        }
    }
}

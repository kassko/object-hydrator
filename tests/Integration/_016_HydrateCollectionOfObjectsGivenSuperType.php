<?php

namespace Kassko\ObjectHydratorTest\Integration;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydratorTest\Integration\Fixture;
use PHPUnit\Framework\TestCase;

class _016_HydrateCollectionOfObjectsGivenSuperType extends TestCase
{
    /**
     * @test
     */
    public function letsGo()
    {
        $primaryData = [
            'cars' => [
                [
                    'id' => 1,
                    'brand' => 'ford',
                    'gasoline_kind' => 'premium',
                ],
                [
                    'id' => 2,
                    'brand' => 'fiesta',
                    'energy_provider' => 'catenary',
                ]
            ]
        ];

        $garage = new class(1) {
            private ?int $id = null;
            /**
             * @BHY\PropertyConfig\CollectionType(
             *      itemClassCandidates=@BHY\ItemClassCandidates(
             *          @BHY\ItemClassCandidate(
             *              value="Kassko\ObjectHydratorTest\Integration\Fixture\Model\Car\GasolinePoweredCar",
             *              discriminatorExpression=@BHY\Expression(value="rawItemDataKeyExists('gasoline_kind')")
             *          ),
             *          @BHY\ItemClassCandidate(
             *              value="Kassko\ObjectHydratorTest\Integration\Fixture\Model\Car\ElectricCar",
             *              discriminatorExpression=@BHY\Expression(value="rawItemDataKeyExists('energy_provider')")
             *          )
             *      )
             * )
             */
            private array $cars = [];

            public function __construct(int $id) { $this->id = $id; }

            public function getId() : int { return $this->id; }

            public function getCars() : array { return $this->cars; }
            public function addCarsItem(Fixture\Model\Car\Car $car) { $this->cars[] = $car; }
        };

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($garage, $primaryData);

        $this->assertSame(1, $garage->getId());

        $cars = $garage->getCars();
        $this->assertCount(2, $cars);

        $car = $cars[0];
        $this->assertInstanceOf(Fixture\Model\Car\GasolinePoweredCar::class, $car);
        $this->assertSame(1, $car->getId());
        $this->assertSame('ford', $car->getBrand());
        $this->assertSame('premium', $car->getGasolineKind());

        $car = $cars[1];
        $this->assertInstanceOf(Fixture\Model\Car\ElectricCar::class, $car);
        $this->assertSame(2, $car->getId());
        $this->assertSame('fiesta', $car->getBrand());
        $this->assertSame('catenary', $car->getEnergyProvider());
    }
}

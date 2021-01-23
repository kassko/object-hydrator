<?php

namespace Kassko\ObjectHydratorTest\Integration;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydratorTest\Integration\Fixture;
use PHPUnit\Framework\TestCase;

class _006_HydratePropertyOfParentClassAndInheritThisParentClassConfiguration extends TestCase
{
    /**
     * @test
     */
    public function letsGo()
    {
        $primaryData = [
            'id' => 1,
            'brand' => 'fiesta',
            'energy_provider' => 'catenary',
        ];

        $car = new Fixture\Model\Car\ElectricCar(1);

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($car, $primaryData);

        $this->assertSame(1, $car->getId());
        $this->assertSame('fiesta', $car->getBrand());
        $this->assertSame('catenary', $car->getEnergyProvider());
    }
}

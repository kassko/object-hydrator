<?php

namespace Kassko\ObjectHydratorTest\Integration;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydratorTest\Integration\Fixture;
use PHPUnit\Framework\TestCase;

class _008_HydratePropertyObjectTypeHintedWithSuperType extends TestCase
{
    /**
     * @test
     */
    public function rawDataWithChildOne()
    {
        $primaryData = [
            'id' => 1,
            'car' => [
                'id' => 1,
                'brand' => 'ford',
                'gasoline_kind' => 'premium',
            ]
        ];

        $garage = new Fixture\Model\Car\Garage;

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($garage, $primaryData);

        $this->assertSame(1, $garage->getId());
        $this->assertNotNull($garage->getCar());
        $this->assertInstanceOf(Fixture\Model\Car\GasolinePoweredCar::class, $garage->getCar());
        $this->assertSame(1, $garage->getCar()->getId());
        $this->assertSame('premium', $garage->getCar()->getGasolineKind());
    }

    /**
     * @test
     */
    public function rawDataWithChildTwo()
    {
        $primaryData = [
            'id' => 1,
            'car' => [
                'id' => 2,
                'brand' => 'fiesta',
                'energy_provider' => 'catenary',
            ]
        ];

        $garage = new Fixture\Model\Car\Garage;

        $hydrator = (new HydratorBuilder())->build();
        $hydrator->hydrate($garage, $primaryData);

        $this->assertSame(1, $garage->getId());
        $this->assertNotNull($garage->getCar());
        $this->assertInstanceOf(Fixture\Model\Car\ElectricCar::class, $garage->getCar());
        $this->assertSame(2, $garage->getCar()->getId());
        $this->assertSame('catenary', $garage->getCar()->getEnergyProvider());
    }
}

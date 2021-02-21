<?php

namespace Kassko\ObjectHydratorIntegrationTest;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, HydratorBuilder};
use Kassko\ObjectHydratorIntegrationTest\Fixture;
use Kassko\ObjectHydratorIntegrationTest\Helper;
use PHPUnit\Framework\TestCase;

class _007_HydratePropertiesOfAUsedTraitAndImportThisTraitConfiguration extends TestCase
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
            'id' => 1,
            'brand' => 'fiesta',
            'energy_provider' => 'catenary',
        ];

        $car = new Fixture\Model\Car\ElectricCarUsingTrait(1);


        $this->hydrator->hydrate($car, $primaryData);

        $this->assertSame(1, $car->getId());
        $this->assertSame('fiesta', $car->getBrand());
        $this->assertSame('catenary', $car->getEnergyProvider());
    }
}

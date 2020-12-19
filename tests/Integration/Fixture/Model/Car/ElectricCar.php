<?php

namespace Big\HydratorTest\Integration\Fixture\Model\Car;

use Big\Hydrator\Annotation\Doctrine as BHY;

class ElectricCar extends Car
{
    private ?string $energyProvider;

    public function getEnergyProvider() : ?string { return $this->energyProvider; }
    public function setEnergyProvider(string $energyProvider) { $this->energyProvider = $energyProvider; }
}

<?php

namespace Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Car;

use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

class ElectricCarUsingTrait
{
    use CarTrait;

    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $energyProvider;


    public function __construct(?int $id = null) { $this->id = $id; }

    public function getId() : ?int { return $this->id; }

    public function getEnergyProvider() : ?string { return $this->energyProvider; }
    public function setEnergyProvider(string $energyProvider) { $this->energyProvider = $energyProvider; }
}

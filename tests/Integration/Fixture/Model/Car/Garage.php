<?php

namespace Big\HydratorTest\Integration\Fixture\Model\Car;

use Big\Hydrator\Annotation\Doctrine as BHY;
use Big\HydratorTest\Integration\Fixture;

class Garage
{
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\Candidates(candidates={
     *      @BHY\PropertyConfig\SingleType(
     *          class="Big\HydratorTest\Integration\Fixture\Model\Car\GasolinePoweredCar",
     *          discriminatorExpression=@BHY\Expression("rawDataKeyExists('car.gasoline_kind')")
     *      ),
     *      @BHY\PropertyConfig\SingleType(
     *          class="Big\HydratorTest\Integration\Fixture\Model\Car\ElectricCar",
     *          discriminatorExpression=@BHY\Expression("rawDataKeyExists('car.energy_provider')")
     *      )
     * })
     */
    private ?Car $car = null;

    public function __construct(?int $id = null) { $this->id = $id; }

    public function getId() : ?int { return $this->id; }

    public function getCar() : ?Car { return $this->car; }
    public function setCar(Car $car) { $this->car = $car; }
}

<?php

namespace Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Car;

use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

class GasolinePoweredCar extends Car
{
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $gasolineKind;


    public function __construct(?int $id = null) { $this->id = $id; }

    public function getId() : ?int { return $this->id; }

    public function getGasolineKind() : ?string { return $this->gasolineKind; }
    public function setGasolineKind(string $gasolineKind) { $this->gasolineKind = $gasolineKind; }
}

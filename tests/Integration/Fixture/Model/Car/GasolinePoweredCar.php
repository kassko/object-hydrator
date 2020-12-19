<?php

namespace Big\HydratorTest\Integration\Fixture\Model\Car;

use Big\Hydrator\Annotation\Doctrine as BHY;

class GasolinePoweredCar extends Car
{
    private ?string $gasolineKind;

    public function getGasolineKind() : ?string { return $this->gasolineKind; }
    public function setGasolineKind(string $gasolineKind) { $this->gasolineKind = $gasolineKind; }
}

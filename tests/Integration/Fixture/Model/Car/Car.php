<?php

namespace Kassko\ObjectHydratorIntegrationTest\Fixture\Model\Car;

use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

abstract class Car
{
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $brand = null;


    public function getBrand() : ?string { return $this->brand; }
    public function setBrand(string $brand) { $this->brand = $brand; }
}

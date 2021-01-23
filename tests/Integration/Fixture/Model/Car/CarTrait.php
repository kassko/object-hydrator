<?php

namespace Kassko\ObjectHydratorTest\Integration\Fixture\Model\Car;

use Kassko\ObjectHydrator\Annotation\Doctrine as BHY;

trait CarTrait
{
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $brand = null;


    public function getBrand() : ?string { return $this->brand; }
    public function setBrand(string $brand) { $this->brand = $brand; }
}

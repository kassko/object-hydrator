<?php

namespace Big\HydratorTest\Integration\Fixture\Model\Car;

use Big\Hydrator\Annotation\Doctrine as BHY;

trait CarTrait
{
    private ?int $id = null;
    /**
     * @BHY\PropertyConfig\SingleType
     */
    private ?string $brand = null;


    public function getId() : ?int { return $this->id; }
    //public function setId(int $id) : self { $this->id = $id; return $this; }

    public function getBrand() : ?string { return $this->brand; }
    public function setBrand(string $brand) { $this->brand = $brand; }
}

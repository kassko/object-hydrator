<?php

namespace Big\HydratorTest\Integration\Fixture\Model\Car;

use Big\Hydrator\Annotation\Doctrine as BHY;

abstract class Car
{
    use CarTrait;//This is to ckeck if hydration of class with traits works fine.

    public function __construct(?int $id = null) { $this->id = $id; }
}

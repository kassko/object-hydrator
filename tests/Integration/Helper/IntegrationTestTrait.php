<?php

namespace Kassko\ObjectHydratorIntegrationTest\Helper;

use Kassko\ObjectHydrator\{Annotation\Doctrine as BHY, Hydrator, HydratorBuilder};

trait IntegrationTestTrait
{
    private Hydrator $hydrator;


    private function initHydrator(array $configs = []) : void
    {
        $builderClass = $_SERVER['BUILDER_CLASS'];

        $this->hydrator = (new $builderClass)->build($configs);
    }
}

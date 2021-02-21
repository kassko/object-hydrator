<?php
namespace Kassko\ObjectHydratorIntegrationTest\Helper;

use Kassko\ObjectHydrator\{Hydrator, HydratorBuilder};

class TestHydratorBuilder extends TestHydratorBuilderAbstract
{
    public function build(array $configs) : Hydrator
    {
        var_dump(__METHOD__);
        return (new HydratorBuilder())->setConfigs($configs)->build();
    }
}

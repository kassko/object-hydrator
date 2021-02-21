<?php
namespace Kassko\ObjectHydratorIntegrationTest\Helper;

use Kassko\ObjectHydrator\Hydrator;

abstract class TestHydratorBuilderAbstract
{
    abstract public function build(array $configs) : Hydrator;
}

<?php

namespace Kassko\ObjectHydrator\Repository;

use Kassko\ObjectHydrator\Model\Method;

/**
 * @author kko
 */
final class MethodCollection
{
    private array $methods = [];

    public function find(string $id) : ?Method
    {
        return isset($this->methods[$id]) ? $this->methods[$id] : null;
    }

    public function add(Method $method) : void
    {
        $this->methods[$method->getId()] = $method;
    }

    public function addCollection(array $methods) : void
    {
        foreach ($methods as $method) {
            $this->add($method);
        }
    }
}

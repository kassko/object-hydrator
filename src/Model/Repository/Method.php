<?php

namespace Kassko\ObjectHydrator\Model\Repository;

use Kassko\ObjectHydrator\Model;

/**
 * @author kko
 */
class Method
{
    private array $methods = [];

    public function find(string $id) : ?Model\Method
    {
        return isset($this->methods[$id]) ? $this->methods[$id] : null;
    }

    public function add(Model\Method $method) : self
    {
        $this->methods[$method->getId()] = $method;

        return $this;
    }

    public function addCollection(array $methods) : self
    {
        foreach ($methods as $method) {
            $this->add($method);
        }

        return $this;
    }
}

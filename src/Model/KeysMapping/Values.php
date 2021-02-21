<?php

namespace Kassko\ObjectHydrator\Model\KeysMapping;

use Kassko\ObjectHydrator\Model\KeysMappingInterface;

/**
 * @author kko
 */
final class Values implements KeysMappingInterface
{
    private array $items;


    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function getItems() : array
    {
        return $this->items;
    }

    public function setItems(array $items) : self
    {
        $this->items = $items;

        return $this;
    }
}

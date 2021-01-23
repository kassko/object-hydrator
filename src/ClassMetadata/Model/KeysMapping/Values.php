<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Model\KeysMapping;

use Kassko\ObjectHydrator\ClassMetadata\Model\KeysMappingInterface;

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

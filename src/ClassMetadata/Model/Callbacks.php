<?php

namespace Big\Hydrator\ClassMetadata\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author kko
 */
final class Callbacks
{
    private ArrayCollection $beforeCollection;
    private ArrayCollection $afterCollection;


    public function __construct()
    {
        $this->beforeCollection = new ArrayCollection;
        $this->afterCollection = new ArrayCollection;
    }

    public function getBeforeCollection() : array
    {
        return $this->beforeCollection->toArray();
    }

    public function addBefore(Method $before)
    {
        $this->beforeCollection->add($before);

        return $this;
    }

    public function getAfterCollection() : array
    {
        return $this->afterCollection->toArray();
    }

    public function addAfter(Method $after)
    {
        $this->afterCollection->add($after);

        return $this;
    }
}

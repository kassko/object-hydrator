<?php

namespace Kassko\ObjectHydrator\Model;

use Kassko\ObjectHydrator\Model\Fragment;

/**
 * @author kko
 */
final class ItemClassCandidate
{
    use Fragment\InstanceCreationAwareTrait;
    use Fragment\RawDataLocationAwareTrait;

    private string $class;
    private ?DynamicValueInterface $discriminator = null;


    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function getClass() : string
    {
        return $this->class;
    }

    public function getDiscriminator() : DynamicValueInterface
    {
        return $this->discriminator;
    }

    public function setDiscriminator(DynamicValueInterface $discriminator) : self
    {
        $this->discriminator = $discriminator;

        return $this;
    }
}

<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine\PropertyConfig;

use Kassko\ObjectHydrator\Annotation\Doctrine\Capability;
use Kassko\ObjectHydrator\Annotation\Doctrine\PropertyConfig;

/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 *
 * @author kko
 */
final class SingleType extends PropertyConfig
{
    use Capability\ToArrayConvertible;

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        foreach ($data as $key => $datum) {
            $this->$key = $datum;
        }
    }
}

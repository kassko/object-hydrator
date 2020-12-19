<?php

namespace Big\Hydrator\Annotation\Doctrine\PropertyConfig;

use Big\Hydrator\Annotation\Doctrine\Capability;
use Big\Hydrator\Annotation\Doctrine\PropertyConfig;

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
        parent::__construct();

        foreach ($data as $key => $datum) {
            $this->$key = $datum;
        }
    }
}

<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author kko
 */
final class DataSources
{
    use Capability\Enabling;
    use Capability\ToArrayConvertible;

    /**
     * One or more DataSource annotations.
     *
     * @var array<\Kassko\ObjectHydrator\Annotation\Doctrine\DataSource>
     */
    public $items = [];
}

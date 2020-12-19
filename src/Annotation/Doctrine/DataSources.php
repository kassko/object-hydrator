<?php

namespace Big\Hydrator\Annotation\Doctrine;

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
     * @var array<\Big\Hydrator\Annotation\Doctrine\DataSource>
     */
    public $items = [];
}

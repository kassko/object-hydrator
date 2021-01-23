<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine\PropertyConfig;

use Kassko\ObjectHydrator\Annotation\Doctrine\Capability;

/**
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author kko
 */
final class Candidates
{
    use Capability\Enabling;
    use Capability\ToArrayConvertible;

    /**
     * One or more DataSource annotations.
     *
     * @var array<\Kassko\ObjectHydrator\Annotation\Doctrine\PropertyConfig>
     */
    public array $candidates = [];
    /**
     * @internal
     * @var array
     */
    public array $variables = [];
}

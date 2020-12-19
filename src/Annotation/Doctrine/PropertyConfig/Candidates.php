<?php

namespace Big\Hydrator\Annotation\Doctrine\PropertyConfig;

use Big\Hydrator\Annotation\Doctrine\Capability;

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
     * @var array<\Big\Hydrator\Annotation\Doctrine\PropertyConfig>
     */
    public array $candidates = [];
    /**
     * @internal
     * @var array
     */
    public array $variables = [];
}

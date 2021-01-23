<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target("ANNOTATION")
 *
 * @author kko
 */
class RawDataLocation
{
    use Capability\ToArrayConvertible;

    /**
     * @internal
     */
    public string $locationName;
    /**
     * @internal
     */
    public ?array $keysMappingValues = null;
    /**
     * @internal
     */
    public ?string $keysMappingPrefix = null;
    /**
     * @internal
     */
    public ?Method $keysMappingMethod = null;
}

<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target("ANNOTATION")
 *
 * @author kko
 */
final class InstanceCreation
{
    use Capability\ToArrayConvertible;

    /**
     * @internal
     * @var string
     */
    public ?string $factoryMethodName = null;
    /**
     * @internal
     */
    public ?Method $factoryMethod = null;
    /**
     * @internal
     */
    public bool $setPropertiesThroughCreationMethodWhenPossible = false;
    /**
     * @internal
     */
    public bool $alwaysAccessPropertiesDirectly = false;
    /**
     * @internal
     */
    public ?Method $afterCreationMethod = null;
    /**
     * @internal
     */
    public ?Methods $afterConstructionMethods = null;
}

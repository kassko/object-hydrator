<?php

namespace Big\Hydrator\Annotation\Doctrine;

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
class Method extends DynamicValueAbstract
{
    use Capability\ToArrayConvertible;

    /**
     * @internal
     */
    public ?string $id = null;
    /**
     * @internal
     */
    public ?string $class = null;
    /**
     * @internal
     */
    public ?string $serviceKey = null;
    /**
     * @internal
     */
    public ?string $name = null;
    /**
     * @internal
     */
    public array $args = [];
    /**
     * @internal
     */
    public bool $magicCallAllowed = false;
}

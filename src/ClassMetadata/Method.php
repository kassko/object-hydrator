<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
class Method implements DynamicValueInterface
{
    //=== Annotations attributes (must be public) : begin ===//
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
    //=== Annotations attributes : end ===//

    private \ReflectionMethod $reflectionMethod;
    private bool $static = false;

    public function setReflector(\ReflectionMethod $reflectionMethod) : self
    {
        $this->reflectionMethod = $reflectionMethod;
        $this->static = $reflectionMethod->isStatic();

        return $this;
    }

    public function getClass() : ?string
    {
        return $this->class;
    }

    public function getServiceKey() : ?string
    {
        return $this->serviceKey;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getArgs() : array
    {
        return $this->args;
    }

    public function isStatic() : ?string
    {
        return $this->static;
    }

    public function isInvokerAService() : ?string
    {
        return null !== $this->serviceKey;
    }

    public function isMagicCallAllowed() : bool
    {
        return $this->magicCallAllowed;
    }
}

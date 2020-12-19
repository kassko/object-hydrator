<?php

namespace Big\Hydrator\Annotation\Doctrine;

/**
 * @author kko
 */
abstract class PropertyConfig
{
    use Capability\Enabling;

    //=== Annotations attributes (must be public) : begin ===//
    /**
     * @internal
     */
    public ?string $keyInRawData = null;
    /**
     * @internal
     */
    public ?string $type = null;
    /**
     * @internal
     */
    public ?string $class = null;
    /**
     * @internal
     */
    //public bool $hydrateRawData = true;
    /**
     * @internal
     */
    public ?DataSource $dataSource = null;
    /**
     * @internal
     */
    public ?string $dataSourceRef = null;
    /**
     * @internal
     */
    public ?Method $discriminatorMethod = null;
    /**
     * @internal
     */
    public ?string $discriminatorMethodRef = null;
    /**
     * @internal
     */
    public ?Expression $discriminatorExpression = null;
    /**
     * @internal
     */
    public ?string $discriminatorExpressionRef = null;
    /**
     * @internal
     */
    public $defaultValue;
    /**
     * @internal
     */
    public ?string $getter = null;
    /**
     * @internal
     */
    public ?string $setter = null;
    /**
     * @internal
     */
    public string $dynamicAttributeMarker = '_';
    /**
     * @internal
     * @var array
     */
    public array $variables = [];
    /**
     * @internal
     * @var \Big\Hydrator\Annotation\Doctrine\Callbacks
     */
    //public ?Callbacks $callbacksUsingMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\Annotation\Doctrine\Callbacks
     */
    //public ?Callbacks $callbacksHydration = null;
    /**
     * @internal
     * @var \Big\Hydrator\Annotation\Doctrine\Callbacks
     */
    //public ?Callbacks $callbacksAssigningHydratedValue = null;
    //=== Annotations attributes : end ===//


    private array $dynamicAttributes = [];
    private array $dynamicAttributesMapping = [];
    private array $forbiddenDynamicAttributes;


    public function __construct(array $data = [])
    {
        $this->forbiddenDynamicAttributes = [
            $this->dynamicAttributeMarker . 'discriminatorRef'
        ];
    }

    public function __set(string $name , $value) : void
    {
        if ($this->dynamicAttributeMarker !== $name[0]) {
            throw new \LogicException(sprintf(
                'Cannot set property "%s::%s" because it does not exists.',
                __CLASS__,
                $name
            ));
        }

        if (isset($this->forbiddenDynamicAttributes[$name])) {
            throw new \LogicException(sprintf(
                'Cannot set such dynamic property "%s::%s" because this dynamic property is forbidden.',
                __CLASS__,
                $name
            ));
        }

        if (! $value instanceof DynamicValueInterface) {
            throw new \LogicException(sprintf(
                'Cannot set dynamic property "%s::%s" with such value because the value must be an instance of "%s" as "%s" or "%s".',
                __CLASS__,
                $name,
                DynamicValueInterface::class,
                Expression::class,
                Method::class
            ));
        }

        //@todo Verify $value is an instance of Method Method or Expression and create Expression annotation.
        $this->dynamicAttributesMapping[$name] = substr($name, 1);
        $this->dynamicAttributes[$this->dynamicAttributesMapping[$name]] = $value;
    }

    public function __get(string $name)
    {
        if ($this->dynamicAttributeMarker !== $name[0]) {
            throw new \LogicException(sprintf(
                'Cannot get attribute "%s::%s" because it does not exists.',
                __CLASS__,
                $name
            ));
        }

        if (! isset($this->dynamicAttributesMapping[$name])) {
            throw new \LogicException(sprintf(
                'Cannot get dynamic attribute "%s::%s" because it does not exists.',
                __CLASS__,
                $name
            ));
        }

        return $this->dynamicAttributes[$this->dynamicAttributesMapping[$name]];
    }
}

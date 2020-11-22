<?php

namespace Big\Hydrator\ClassMetadata;

/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 *
 * @author kko
 */
final class Property
{
    use Capability\Enabling;

    private const LOADING_LAZY = 'LAZY';
    private const LOADING_EAGER = 'EAGER';

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
    public bool $collection = false;
    /**
     * @internal
     */
    public bool $hydrateRawData = true;
    /**
     * @internal
     */
    public ?string $dataSourceRef = null;
    /**
     * @internal
     */
    public ?string $conditionalRef = null;
    /**
     * @internal
     * @var string
     */
    public string $loading = self::LOADING_LAZY;
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
    public ?string $adder = null;
    /**
     * @internal
     */
    public string $dynamicAttributeMarker = '_';
    /**
     * @internal
     * @var array
     */
    public array $variables = [];
    //=== Annotations attributes : end ===//

    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $beforeUsingLoadedMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $afterUsingLoadedMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $beforeHydration = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $afterHydration = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $beforeSettingHydratedValue = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $afterSettingHydratedValue = null;

    private string $name;
    private ?string $adderNameFormat = null;
    private ?DataSource $dataSource = null;
    private ?Conditional $conditional = null;
    private array $methods = [];

    private array $dynamicAttributes = [];
    private array $dynamicAttributesMapping = [];
    private array $forbiddenDynamicAttributes;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $datum) {
            $this->$key = $datum;
        }

        $this->forbiddenDynamicAttributes = [
            $this->dynamicAttributeMarker . 'conditionalRef'
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

    public function compile(\ReflectionProperty $reflectionProperty, array $extraData) : self
    {
        $this->name = $reflectionProperty->getName();

        if ($reflectionProperty->hasType()) {
            if (null === $this->class && ! $reflectionProperty->getType()->isBuiltIn()) {
                $this->class = $reflectionProperty->getType()->getName();
                $this->type = 'object';
            } elseif (null === $this->type && $reflectionProperty->getType()->isBuiltIn()) {
                $this->type = $reflectionProperty->getType()->getName();
            }
        }

        if (null === $this->keyInRawData) {
            $this->keyInRawData = $reflectionProperty->getName();
        }

        if (null === $this->getter) {
            $this->getter = $this->getterise($reflectionProperty->getName());
        }

        if (null === $this->setter) {
            $this->setter = $this->setterise($reflectionProperty->getName());
        }

        if ($this->collection) {
            /*if (null === $this->class) {
                throw new \LogicException(sprint(
                    'Cannot interpret properly property "%s" config.' .
                    . PHP_EOL . 'You must specify "class" attribute.',
                    $this->name
                ));
            }*/

            if (null === $this->adder) {
                $this->adder = $this->adderise($reflectionProperty->getName(), $extraData['default_adder_name_format']);
            }
        }

        return $this;
    }

    /*private function setContainingClassMethods(array $methods) : self
    {
        $this->methods = array_flip($methods);
        return $this;
    }*/

    public function getName() : string
    {
        return $this->name;
    }

    public function getKeyInRawData() : string
    {
        return $this->keyInRawData;
    }

    public function isObject() : bool
    {
        return isset($this->class);
    }

    public function getClass() : string
    {
        return $this->class;
    }

    public function isCollection() : bool
    {
        return $this->collection;
    }

    public function areRawDataToHydrate() : bool
    {
        return $this->hydrateRawData;
    }

    public function hasDataSourceRef() : bool
    {
        return isset($this->dataSourceRef);
    }

    public function getDataSourceRef() : ?string
    {
        return $this->dataSourceRef;
    }

    public function hasDataSource() : bool
    {
        return null !== $this->dataSource;
    }

    public function setDataSource(DataSource $dataSource) : self
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    public function getDataSource() : ?DataSource
    {
        return $this->dataSource;
    }

    public function hasConditionalRef() : bool
    {
        return isset($this->conditionalRef);
    }

    public function getConditionalRef() : ?string
    {
        return $this->conditionalRef;
    }

    public function hasConditional() : bool
    {
        return null !== $this->conditional;
    }

    public function setConditional(Conditional $conditional) : self
    {
        $this->conditional = $conditional;
        return $this;
    }

    public function getConditional() : ?Conditional
    {
        return $this->conditional;
    }

    public function mustBeLazyLoaded() : bool
    {
        return self::LOADING_LAZY === $this->loading;
    }

    public function mustBeEagerLoaded() : bool
    {
        return self::LOADING_EAGER === $this->loading;
    }

    public function hasDefaultValue() : bool
    {
        return isset($this->defaultValue);
    }

    public function getDefaultValue() : ?string
    {
        return $this->defaultValue;
    }

    public function hasVariables() : bool
    {
        return count($this->variables) > 0;
    }

    public function getVariables() : array
    {
        return $this->variables;
    }

    public function hasGetter() : bool
    {
        return null !== $this->getGetter();
    }

    public function getGetter() : ?string
    {
        return $this->getter;
    }

    public function hasSetter() : bool
    {
        return null !== $this->getSetter();
    }

    public function getSetter() : ?string
    {
        return $this->setter;
    }

     public function hasAdder() : bool
    {
        return null !== $this->getAdder();
    }

    public function getAdder() : ?string
    {
        return $this->adder;
    }

    public function isAssocAdder() : bool
    {
        return $this->assocAdder;
    }

    private function getterise(string $propertyName) : ?string
    {
        static $defaultsGettersTypes = ['get', 'is', 'has'];

        foreach ($defaultsGettersTypes as $getterType) {
            $getter = $getterType.ucfirst($propertyName);
            if (isset($this->methods[$getter])) {
                return $this->methods[$getter];
            }
        }

        return null;
    }

    private function setterise(string $propertyName) : ?string
    {
        $setter = 'set'.ucfirst($propertyName);

        if (isset($this->methods[$setter])) {
            return $setter;
        }

        return null;
    }

    private function adderise(string $propertyName, ?string $defaultAdderNameFormat) : ?string
    {
        $adder = sprintf($defaultAdderNameFormat, ucfirst($propertyName));

        if (isset($this->methods[$adder])) {
            return $adder;
        }

        return null;
    }

    public function getDynamicAttributes() : array
    {
        return $this->dynamicAttributes;
    }

    public function getBeforeUsingLoadedMetadata() : ?Methods
    {
        return $this->beforeUsingLoadedMetadata;
    }

    public function getAfterUsingLoadedMetadata() : ?Methods
    {
        return $this->afterUsingLoadedMetadata;
    }

    public function getBeforeHydration() : ?Methods
    {
        return $this->beforeHydration;
    }

    public function getAfterHydration() : ?Methods
    {
        return $this->afterHydration;
    }

    public function getBeforeSettingHydratedValue() : ?Methods
    {
        return $this->beforeSettingHydratedValue;
    }

    public function getAfterSettingHydratedValue() : ?Methods
    {
        return $this->afterSettingHydratedValue;
    }
}

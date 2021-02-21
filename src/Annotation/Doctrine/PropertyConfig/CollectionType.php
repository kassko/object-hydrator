<?php

namespace Kassko\ObjectHydrator\Annotation\Doctrine\PropertyConfig;

use Kassko\ObjectHydrator\Annotation\Doctrine\Capability;
use Kassko\ObjectHydrator\Annotation\Doctrine\{ItemClassCandidate, ItemClassCandidates, PropertyConfig};

/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 *
 * @author kko
 */
final class CollectionType extends PropertyConfig
{
    use Capability\ToArrayConvertible;

    /**
     * @internal
     */
    public ?string $adder = null;
    /**
     * @internal
     */
    public ?string $itemsClass = null;
    /**
     * @internal
     * @var ItemClassCandidates
     */
    public ?ItemClassCandidate $itemClassCandidate = null;
    /**
     * @internal
     * @var ItemClassCandidates
     */
    public ?ItemClassCandidates $itemClassCandidates = null;

    //private ?string $adderNameFormat = null;


    public function __construct(array $data = [])
    {
        parent::__construct();

        foreach ($data as $key => $datum) {
            $this->$key = $datum;
        }
    }

    public function isCollection() : bool
    {
        return true;
    }

    public function isObject() : bool
    {
        return null !== $this->itemsClass
        || null !== $this->itemClassCandidates;
    }

    public function compile(\ReflectionProperty $reflectionProperty, array $extraData = []) : CollectionType
    {
        parent::compile($reflectionProperty, $extraData);

        if ($reflectionProperty->hasType()) {
            if (null === $this->type && true === $reflectionProperty->getType()->isBuiltIn()) {
                if ('array' === $this->type) {
                    $this->collection = true;
                }
            }
        }

        if (null === $this->adder) {
            $this->adder = $this->adderise($reflectionProperty->getName(), $extraData['default_adder_name_format']);
        }

        /*if (null === $this->itemsClass && null === $this->itemClassCandidates) {
            throw new \LogicException(sprintf(
                'Cannot interpret properly configuration of property "%s".' .
                PHP_EOL . 'You must provide the class of items of the collection.' .
                PHP_EOL . 'And you can specify a specific class for some given items of the collection' .
                PHP_EOL . 'throw items class candidates.',
                $reflectionProperty->getName()
            ));
        }*/

        return $this;
    }

    public function getItemsClass() : string
    {
        return $this->itemsClass;
    }

    public function hasItemClassCandidates() : bool
    {
        return null !== $this->itemClassCandidates;
    }

    public function getItemClassCandidates() : ?ItemClassCandidates
    {
        return $this->itemClassCandidates;
    }


    public function hasAdder() : bool
    {
        return null !== $this->getAdder();
    }

    public function getAdder() : ?string
    {
        return $this->adder;
    }

    private function adderise(string $propertyName, ?string $defaultAdderNameFormat) : ?string
    {
        $adder = sprintf($defaultAdderNameFormat, ucfirst($propertyName));

        if (isset($this->methods[$adder])) {
            return $adder;
        }

        return null;
    }
}

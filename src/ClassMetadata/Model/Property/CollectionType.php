<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Model\Property;

use Kassko\ObjectHydrator\{ExpressionEvaluator, MethodInvoker};
use Kassko\ObjectHydrator\ClassMetadata\Dto;
use Kassko\ObjectHydrator\ClassMetadata\Model;

/**
 * @author kko
 */
final class CollectionType extends Leaf
{
    private ?string $adder = null;
    private ?string $itemsClass = null;
    private array $itemClassCandidates = [];


    public function isCollection() : bool
    {
        return true;
    }

    /*public function isObject() : bool
    {
        return null !== $this->itemsClass || count($this->itemClassCandidates);
    }*/

    public function hasAdder() : bool
    {
        return null !== $this->adder;
    }

    public function getAdder() : ?string
    {
        return $this->adder;
    }

    public function setAdder(string $adder) : self
    {
        $this->adder = $adder;

        return $this;
    }

    public function getItemsClass() : string
    {
        return $this->itemsClass;
    }

    public function setItemsClass(string $itemsClass) : self
    {
        $this->itemsClass = $itemsClass;

        return $this;
    }

    public function hasItemClassCandidates() : bool
    {
        return count($this->itemClassCandidates);
    }

    public function getItemClassCandidates() : array
    {
        return $this->itemClassCandidates;
    }

    public function addItemClassCandidate(Model\ItemClassCandidate $itemClassCandidate) : self
    {
        $this->itemClassCandidates[] = $itemClassCandidate;

        return $this;
    }

    public function getCurrentItemCollectionClass(ExpressionEvaluator $expressionEvaluator, MethodInvoker $methodInvoker) : ?Dto\ClassInfo
    {
        foreach ($this->itemClassCandidates as $itemClassCandidate) {
            $value = $itemClassCandidate->getDiscriminator();

            if ($value->isExpression() && true === $expressionEvaluator->resolveAdvancedExpression($value)) {
                return new Dto\ClassInfo($itemClassCandidate->getClass(), $itemClassCandidate->getRawDataLocation());
            }

            if ($value->isMethod() && true === $methodInvoker->invokeMethod($value)) {
                return new Dto\ClassInfo($itemClassCandidate->getClass(), $itemClassCandidate->getRawDataLocation());
            }
        }

        return new Dto\ClassInfo($this->itemsClass);
    }
}

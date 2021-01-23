<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadata;
use Kassko\ObjectHydrator\ClassMetadata\Model\Property\{Candidates, Leaf};

use function sprintf;

class PropertyCandidatesResolver
{
    private ExpressionEvaluator $expressionEvaluator;
    private MethodInvoker $methodInvoker;


    public function __construct(ExpressionEvaluator $expressionEvaluator, MethodInvoker $methodInvoker)
    {
        $this->expressionEvaluator = $expressionEvaluator;
        $this->methodInvoker = $methodInvoker;
    }

    public function resolveGoodCandidate(Candidates $propertyCandidates) : Leaf
    {
        foreach ($propertyCandidates->getCandidates() as $candidateProperty) {
            if (! $candidateProperty->hasDiscriminator()) {
                return $candidateProperty;
            }

            $discriminator = $candidateProperty->getDiscriminator();

            if ($discriminator->isExpression()) {
                if ($this->expressionEvaluator->resolveAdvancedExpression($discriminator)) {
                    return $candidateProperty;
                }
            } elseif ($discriminator->isMethod()) {
                if ($this->methodInvoker->invokeMethod($discriminator)) {
                    return $candidateProperty;
                }
            }
        }

        throw new \LogicException(sprintf(
            'Cannot resolve version of property metadata to use for property "%s".',
            $candidateProperty->getName()
        ));
    }
}

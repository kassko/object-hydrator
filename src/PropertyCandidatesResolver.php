<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadata;
use Big\Hydrator\ClassMetadata\PropertyCandidates;

use function sprintf;

class PropertyCandidatesResolver
{
    private ExpressionEvaluator $expressionEvaluator;
    private MethodInvoker $invoker;

    public function __construct(ExpressionEvaluator $expressionEvaluator, MethodInvoker $methodInvoker)
    {
        $this->expressionEvaluator = $expressionEvaluator;
        $this->methodInvoker = $methodInvoker;
    }

    public function resolveGoodCandidate(PropertyCandidates $propertyCandidates)
    {
        foreach ($propertyCandidates->items as $candidateProperty) {
            if (! $candidateProperty->hasConditional()) {
                return $candidateProperty;
            }

            $conditionalValue = $candidateProperty->getConditional()->getValue();
            if ($conditionalValue->getValue() instanceof ClassMetadata\Expression) {
                if ($this->expressionEvaluator->resolveAdvancedExpression($conditionalValue)) {
                    return $candidateProperty;
                }
            } elseif ($conditionalValue instanceof ClassMetadata\Method) {
                if ($this->methodInvoker->invokeMethod($conditionalValue)) {
                    return $candidateProperty;
                }
            }
        }

        throw new \LogicException(sprintf(
            'Cannot resolve version of property metadata to use for property "%s".',
            $candidatePropertys->getName()
        ));
    }
}

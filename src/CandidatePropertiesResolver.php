<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadata\Conditional;
use Big\Hydrator\ClassMetadata\PropertyVersions;

class CandidatePropertiesResolver
{
    private ExpressionEvaluator $expressionEvaluator;
    private MethodInvoker $invoker;

    public function __construct(ExpressionEvaluator $expressionEvaluator, MethodInvoker $methodInvoker)
    {
        $this->expressionEvaluator = $expressionEvaluator;
        $this->methodInvoker = $methodInvoker;
    }

    public function resolveVersionToUse(PropertyVersions $candidateProperties)
    {
        var_dump($candidateProperties->getName());
        foreach ($candidateProperties->items as $candidateProperty) {
            if (! $candidateProperty->hasConditional()) {
                var_dump('ICI1');
                return $candidateProperty;
            }

            $conditional = $candidateProperty->getConditional();
            if ($conditional instanceof Conditional\Expression) {
                if ($this->expressionEvaluator->resolveAdvancedExpression($conditional->getExpression())) {
                    var_dump('ICI2  ' . $conditional->getId());
                    return $candidateProperty;
                }
            } elseif ($conditional instanceof Conditional\Method) {
                if ($this->methodInvoker->invokeMethod($conditional->getMethod())) {
                    var_dump('ICI3  '  . $conditional->getId());
                    return $candidateProperty;
                }
            }
        }

        throw new \LogicException(sprintf(
            'Cannot resolve version of property metadata to use for property "%s".',
            $candidateProperties->getName()
        ));
    }
}

<?php

namespace Big\Hydrator;

class PropertyMetadataVersionResolver
{
    private ExpressionEvaluator $expressionEvaluator;
    private MethodInvoker $invoker;

    public function __construct(ExpressionEvaluator $expressionEvaluator, MethodInvoker $methodInvoker)
    {
        $this->expressionEvaluator = $expressionEvaluator;
        $this->methodInvoker = $methodInvoker;
    }

    public function resolveVersionToUse(array $propertyVersions)
    {
        foreach ($propertyVersions->getVersions() as $propertyVersion) {
            if (! $propertyVersion->hasConditional()) {
                return $propertyVersion;
            }

            $conditional = $propertyVersion->getConditional();
            if ($conditional instanceof ConditionalMethod) {
                if ($this->expressionEvaluator->resolveAdvancedExpression($conditional->getExpression())) {
                    return $propertyVersion;
                }
            } elseif ($conditional instanceof ConditionalExpression) {
                if ($this->methodInvoker->invokeMethod($conditional->getMethod())) {
                    return $propertyVersion;
                }
            }
        }

        throw new \LogicException(sprintf(
            'Cannot resolve version of property metadata to use for property "%s".',
            $propertyVersions->getName()
        ));
    }
}

<?php

namespace Big\Hydrator;

use Symfony\Component\ExpressionLanguage\{ExpressionFunction, ExpressionFunctionProviderInterface, ExpressionLanguage};
use Big\Hydrator\{ClassMetadata, Config, MemberAccessStrategyFactory};

use function sprintf;

/**
* ExpressionFunctionProvider
*
* @author kko
*/
class ExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'object',
                function () {
                    return 'object';
                },
                function (array $context) {
                    return $context['object'];
                }
            ),
            new ExpressionFunction(
                'normalized_data',
                function () {
                    return 'normalized_data';
                },
                function (array $context) {
                    return $context['normalized_data'];
                }
            ),
            new ExpressionFunction(
                'variables',
                function ($arg) {
                    return 'current_variables';
                },
                function (array $context, $value) {
                    return $context['current_variables'];
                }
            ),
            new ExpressionFunction(
                'property',
                function ($arg) {
                    return sprintf('provider.loadPropertyAndGetValue(%s)', $arg);
                },
                function (array $context, $propertyName) {
                    return $context['provider']->loadPropertyAndGetValue($propertyName);
                }
            ),
            new ExpressionFunction(
                'strictlyProperty',
                function ($arg) {
                    return sprintf('provider.getPropertyValue(%s)', $arg);
                },
                function (array $context, $propertyName) {
                    return $context['provider']->getPropertyValue($propertyName);
                }
            ),
            new ExpressionFunction(
                'service',
                function ($arg) {
                    return sprintf('provider.resolveService(%s)', $arg);
                },
                function (array $context, $serviceKey) {
                    return $context['provider']->resolveService($serviceKey);
                }
            ),
            new ExpressionFunction(
                'source',
                function ($arg) {
                    return sprintf('provider.fetchDataSource(%s)', $arg);
                },
                function (array $context, $dataSourceId) {
                    return $context['provider']->fetchDataSource($dataSourceId);
                }
            ),
            new ExpressionFunction(
                'sourceByTag',
                function ($arg) {
                    return sprintf('provider.fetchDataSourcesByTag(%s)', $arg);
                },
                function (array $context, $dataSourceTag) {
                    return $context['provider']->fetchDataSourcesByTag($dataSourceTag);
                }
            ),
            new ExpressionFunction(
                'variable',
                function ($arg) {
                    return sprintf('current_variables[%s]');
                },
                function (array $context, $variableKey) {
                    return $context['current_variables'][$variableKey];
                }
            ),
            /*new ExpressionFunction(
                'parentObject',
                function ($arg) {
                    return sprintf('provider.resolveParentObject(%s)', $arg);
                },
                function (array $context, $value) {
                    return $context['provider']->resolveParentObject($value);
                }
            ),*/

        ];
    }
}

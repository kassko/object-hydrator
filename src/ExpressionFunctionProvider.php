<?php

namespace Big\Hydrator;

use Big\Hydrator\{ClassMetadata, Config, MemberAccessStrategyFactory};
use Symfony\Component\ExpressionLanguage\{ExpressionFunction, ExpressionFunctionProviderInterface, ExpressionLanguage};

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
                'this',
                function () {
                    return 'this';
                },
                function (array $context) {
                    return $context['this'];
                }
            ),
            new ExpressionFunction(
                'rawData',
                function () {
                    return 'normalized_data';
                },
                function (array $context) {
                    return $context['normalized_data'];
                }
            ),
            new ExpressionFunction(
                'variables',
                function () {
                    return 'current_variables';
                },
                function (array $context) {
                    return $context['current_variables'];
                }
            ),
            new ExpressionFunction(
                'property',
                function (string $propertyName) {
                    return sprintf('provider.loadPropertyAndGetValue(%s)', $propertyName);
                },
                function (array $context, string $propertyName) {
                    return $context['provider']->loadPropertyAndGetValue($propertyName);
                }
            ),
            new ExpressionFunction(
                'directProperty',
                function (string $propertyName) {
                    return sprintf('provider.getPropertyValue(%s)', $propertyName);
                },
                function (array $context, string $propertyName) {
                    return $context['provider']->getPropertyValue($propertyName);
                }
            ),
            new ExpressionFunction(
                'service',
                function (string $serviceKey) {
                    return sprintf('provider.resolveService(%s)', $serviceKey);
                },
                function (array $context, string $serviceKey) {
                    return $context['provider']->resolveService($serviceKey);
                }
            ),
            new ExpressionFunction(
                'source',
                function (string $dataSourceId) {
                    return sprintf('provider.fetchDataSource(%s)', $dataSourceId);
                },
                function (array $context, string $dataSourceId) {
                    return $context['provider']->fetchDataSource($dataSourceId);
                }
            ),
            new ExpressionFunction(
                'sourceByTag',
                function (string $dataSourceTag) {
                    return sprintf('provider.fetchDataSourcesByTag(%s)', $dataSourceTag);
                },
                function (array $context, string $dataSourceTag) {
                    return $context['provider']->fetchDataSourcesByTag($dataSourceTag);
                }
            ),
            new ExpressionFunction(
                'variable',
                function (string $variableKey) {
                    return sprintf('current_variables[%s]', $variableKey);
                },
                function (array $context, string $variableKey) {
                    return $context['current_variables'][$variableKey];
                }
            ),
            new ExpressionFunction(
                'rawDataKeyExists',
                function ($key) {
                    return sprintf('provider.arrayKeyExists(%s, normalized_data)', $key);
                },
                function (array $context, $key) {
                    return $context['provider']->arrayKeyExists($key, $context['normalized_data']);
                }
            ),
            new ExpressionFunction(
                'rawDataIsSet',
                function ($key) {
                    return sprintf('provider.arrayKeyIsSet(%s, normalized_data)', $key);
                },
                function (array $context, $key) {
                    return $context['provider']->arrayKeyIsSet($key, $context['normalized_data']);
                }
            ),
            new ExpressionFunction(
                'rawDataIsCollectionOfItems',
                function () {
                    return 'provider.arrayIsCollectionOfItems(normalized_data)';
                },
                function (array $context) {
                    return $context['provider']->arrayIsCollectionOfItems($context['normalized_data']);
                }
            ),
            new ExpressionFunction(
                'rawDataHasPair',
                function ($key, $value) {
                    return sprintf('provider.arrayHasPair(%s, %s, normalized_data)', $key, $value);
                },
                function (array $context, $value) {
                    return $context['provider']->arrayHasPair($key, $value, $context['normalized_data']);
                }
            ),
            new ExpressionFunction(
                'rawDataHasPairStrict',
                function ($key, $value) {
                    return sprintf('provider.arrayHasPairStrict(%s, %s, normalized_data)', $key, $value);
                },
                function (array $context, $key, $value) {
                    return $context['provider']->arrayHasPairStrict($key, $value, $context['normalized_data']);
                }
            ),
            new ExpressionFunction(
                'rawDataKeysExists',
                function (...$keys) {
                    return sprintf('provider.arrayKeysExists([%s], normalized_data)', implode(',', $keys));
                },
                function (array $context, ...$keys) {
                    return $context['provider']->arrayKeysExists($keys, $context['normalized_data']);
                }
            ),
            new ExpressionFunction(
                'rawItemDataKeyExists',
                function ($key) {
                    return sprintf('provider.arrayKeyExists(%s, normalized_item_data)', $key);
                },
                function (array $context, $key) {
                    return $context['provider']->arrayKeyExists($key, $context['normalized_item_data']);
                }
            ),
            new ExpressionFunction(
                'rawItemDataIsSet',
                function ($key) {
                    return sprintf('provider.arrayKeyIsSet(%s, normalized_item_data)', $key);
                },
                function (array $context, $key) {
                    return $context['provider']->arrayKeyIsSet($key, $context['normalized_item_data']);
                }
            ),
            new ExpressionFunction(
                'rawItemDataIsCollectionOfItems',
                function () {
                    return 'provider.arrayIsCollectionOfItems(normalized_item_data)';
                },
                function (array $context) {
                    return $context['provider']->arrayIsCollectionOfItems($context['normalized_item_data']);
                }
            ),
            new ExpressionFunction(
                'rawItemDataHasPair',
                function ($key, $value) {
                    return sprintf('provider.arrayHasPair(%s, %s, normalized_item_data)', $key, $value);
                },
                function (array $context, $value) {
                    return $context['provider']->arrayHasPair($key, $value, $context['normalized_item_data']);
                }
            ),
            new ExpressionFunction(
                'rawItemDataHasPairStrict',
                function ($key, $value) {
                    return sprintf('provider.arrayHasPairStrict(%s, %s, normalized_item_data)', $key, $value);
                },
                function (array $context, $key, $value) {
                    return $context['provider']->arrayHasPairStrict($key, $value, $context['normalized_item_data']);
                }
            ),
            new ExpressionFunction(
                'rawItemDataKeysExists',
                function (...$keys) {
                    return sprintf('provider.arrayKeysExists([%s], normalized_item_data)', implode(',', $keys));
                },
                function (array $context, ...$keys) {
                    return $context['provider']->arrayKeysExists($keys, $context['normalized_item_data']);
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

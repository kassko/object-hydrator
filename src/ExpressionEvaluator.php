<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadata;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function get_class;
use function gettype;
use function is_object;
use function is_scalar;
use function preg_match;
use function sprintf;
use function substr;

class ExpressionEvaluator
{
    private ExpressionContext $expressionContext;
    private Config $expressionConfig;
    private ?ExpressionLanguage $expressionLanguage;


    public function __construct(ExpressionContext $expressionContext, Config $expressionConfig, ?ExpressionLanguage $expressionLanguage = null)
    {
        $this->expressionContext = $expressionContext;
        $this->expressionConfig = $expressionConfig;
        $this->expressionLanguage = $expressionLanguage;

        if ($expressionLanguage) {
            $expressionLanguage->registerProvider(new ExpressionFunctionProvider);
        }
    }

    public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider)
    {
        if ($expressionLanguage) {
            $expressionLanguage->registerProvider($provider);
        } else {
            throw new \LogicException(
                'Cannot use the added expression language provider ' .
                'because symfony/expression-language is not installed.'
            );
        }
    }

    public function resolveExpressions(array $args) : array
    {
        foreach ($args as &$arg) {
            if ($arg instanceof ClassMetadata\Model\Expression) {
                $arg = $this->resolveAdvancedExpression($arg);
            } else {
                $arg = $this->resolveExpression($arg);
            }
        }

        return $args;
    }

    public function resolveExpression($arg)
    {
        if (empty($arg)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cannot resolve an expression in metadata of class "%s". Expected an not empty expression.',
                    get_class($this->expressionContext['object'])
                )
            );
        }

        if (!is_scalar($arg)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cannot resolve an expression in metadata of class "%s". Expected a scalar expression but got one of type "%s".',
                    get_class($this->expressionContext['object']),
                    is_object($arg) ? get_class($arg) : gettype($arg)
                )
            );
        }

        if ($this->expressionConfig['keywords']['this_keyword'] === $arg) {
            return $this->expressionContext['object'];
        }

        if ($this->expressionConfig['keywords']['this_norm_keyword'] === $arg) {
            return $this->expressionContext['normalized_data'];
        }

        if ($this->expressionConfig['keywords']['variables_keyword'] === $arg) {
            return $this->expressionContext['current_variables'];
        }

        if ($this->expressionConfig['markers']['property_marker'] === $arg[0]) {
            return $this->expressionContext['provider']->loadPropertyAndGetValue(substr($arg, $this->expressionConfig['markers']['property_marker_size']));
        }

        if ($this->expressionConfig['markers']['direct_property_marker'] === substr($arg, 0, $this->expressionConfig['markers']['direct_property_marker_size'])) {
            return $this->expressionContext['provider']->getPropertyValue(substr($arg, $this->expressionConfig['markers']['direct_property_marker_size']));
        }

        if ($this->expressionConfig['markers']['service_marker'] === $arg[0]) {
            return $this->expressionContext['provider']->resolveService(substr($arg, $this->expressionConfig['markers']['service_marker_size']));
        }

        if ($this->expressionConfig['markers']['source_marker'] === substr($arg, 0, $this->expressionConfig['markers']['source_marker_size'])) {
            return $this->expressionContext['provider']->fetchDataSource(substr($arg, $this->expressionConfig['markers']['source_marker_size']));
        }

        if ($this->expressionConfig['markers']['source_tag_marker'] === substr($arg, 0, $this->expressionConfig['markers']['source_tag_marker_size'])) {
            return $this->expressionContext['provider']->fetchDataSourcesByTag(substr($arg, $this->expressionConfig['markers']['source_tag_marker_size']));
        }

        if ($this->expressionConfig['markers']['variable_marker'] === substr($arg, 0, $this->expressionConfig['markers']['variable_marker_size'])) {
            return $this->expressionContext['current_variables'][substr($arg, $this->expressionConfig['markers']['variable_marker_size'])];
        }

        return $this->resolveAdvancedExpression($arg);
    }

    public function resolveAdvancedExpression($arg)
    {
        if (! $arg instanceof ClassMetadata\Model\Expression) {
            throw new \Exception(sprintf(
                'Cannot resolve an expression in metadata of class "%s".'
                . PHP_EOL . 'An instance of %s was expected but got "%s"',
                get_class($this->expressionContext['object']),
                Expression::class,
                is_scalar($arg) ? $arg : (is_object($arg) ? get_class($arg) : gettype($arg))
            ));
        }

        if (null === $this->expressionLanguage) {
            throw new Exception\NotProvidedExpressionEvaluatorException(
                sprintf(
                    'Cannot resolve such expression "%s" in metadata of class "%s". You must install the component "symfony/expression-language".',
                    $arg->getValue(),
                    get_class($this->expressionContext['object'])
                )
            );
        }

        return $this->expressionLanguage->evaluate($arg->getValue(), $this->expressionContext->getValues());
    }
}

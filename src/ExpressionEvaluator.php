<?php

namespace Big\Hydrator;

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
    /**
     * @var ExpressionFunctionProviderInterface[]
     */
    protected array $expressionLanguageProviders;

    public function __construct(ExpressionContext $expressionContext, Config $expressionConfig, ?ExpressionLanguage $expressionLanguage = null)
    {
        $this->expressionContext = $expressionContext;
        $this->expressionConfig = $expressionConfig;
        $this->expressionLanguage = $expressionLanguage;
        $this->expressionLanguageProviders = null === $expressionLanguage ? [] : [new ExpressionFunctionProvider];
    }

    public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider)
    {
        $this->expressionLanguageProviders[] = $provider;
    }

    public function resolveExpressions(array $args) : array
    {
        foreach ($args as &$arg) {
            $arg = $this->resolveExpression($arg);
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

        if (! is_scalar($arg)) {
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
        $expressionDetected = preg_match('/expr\((.+)\)/', $arg, $matches);

        if (1 !== $expressionDetected) {
            return $arg;
        }

        if (null === $this->expressionLanguage) {
            throw new Exception\NotProvidedExpressionEvaluatorException(
                sprintf(
                    'Cannot resolve such expression "%s" in metadata of class "%s". You must install the component "symfony/expression-language".',
                    $matches[1],
                    get_class($this->expressionContext['object'])
                )
            );
        }

        return $this->expressionLanguage->evaluate($matches[1], $this->expressionContext->geVariables());
    }
}

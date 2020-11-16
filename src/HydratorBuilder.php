<?php

namespace Big\Hydrator;

use Big\Hydrator\ConfigValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function class_exists;

class HydratorBuilder
{
    private array $configs = [];

    public function build() : \Big\Hydrator\Hydrator
    {
        $config = new \Big\Hydrator\Config($this->getValidatedConfig());

        $classMetadataLoader = new \Big\Hydrator\ClassMetadataLoader(
            (new \Big\Hydrator\LoaderResolver)->addLoader(
                (
                    new \Big\Hydrator\ClassMetadataLoader\DoctrineAnnotationLoader(
                        new \Doctrine\Common\Annotations\AnnotationReader
                    )
                )->setConfig(
                    new \Big\Hydrator\ClassMetadataConfig($config['class_metadata'])
                )
            )
        );

        $expressionContext = new \Big\Hydrator\ExpressionContext;
        $expressionEvaluator = new \Big\Hydrator\ExpressionEvaluator(
            $expressionContext,
            $config->getPartition('data_source_expressions'),
            class_exists(ExpressionLanguage::class, true) ? new ExpressionLanguage : null
        );

        $methodInvoker = new \Big\Hydrator\MethodInvoker($expressionEvaluator);

        $methodInvoker->setServiceLocator($config['service_locator']);

        $dataFetcher = new \Big\Hydrator\DataFetcher($methodInvoker);

        $propertyMetadataVersionResolver = new \Big\Hydrator\CandidatePropertiesResolver($expressionEvaluator, $methodInvoker);

        $memberAccessStrategyFactory = (
            new \Big\Hydrator\MemberAccessStrategyFactory
        )->setCandidatePropertiesResolver($propertyMetadataVersionResolver);

        $objectLoadabilityChecker = new \Big\Hydrator\ObjectLoadabilityChecker;

        $hydrator = (new \Big\Hydrator\Hydrator(
            $classMetadataLoader,
            $memberAccessStrategyFactory,
            new \Big\Hydrator\IdentityMap($objectLoadabilityChecker),
            $objectLoadabilityChecker,
            $dataFetcher,
            $methodInvoker,
            $expressionContext,
            new \Big\Hydrator\EssentialDataProvider(
                $dataFetcher,
                $memberAccessStrategyFactory,
                $config['service_locator']
            ),
            $config
        ))->setPropertyMetadataVersionresolver($propertyMetadataVersionResolver);

        $logger = isset($config['logger_key']) ? ($config['service_locator'])($config['logger_key']) : null;
        $this->initializeRegistry($hydrator, $logger);

        return $hydrator;
    }

    public function addConfig(array $config) : self
    {
        $this->configs[] = $config;

        return $this;
    }

    public function setConfigs(array $configs) : self
    {
        $this->configs = $configs;

        return $this;
    }

    private function initializeRegistry(Hydrator $hydrator, ?LoggerInterface $logger) : void
    {
        Registry::getInstance()[Registry::KEY_LOGGER] = $logger ?? new \Psr\Log\NullLogger;

        $propertyLoader = new PropertyLoader($hydrator);
        Registry::getInstance()[Registry::KEY_PROPERTY_LOADER] = $propertyLoader;
    }

    private function getValidatedConfig() : array
    {
        $processor = new Processor();
        $configValidator = new ConfigValidator();

        $validatedConfig = $processor->processConfiguration(
            $configValidator,
            $this->configs
        );

        $this->finalizeConfig($validatedConfig);

        return $validatedConfig;
    }

    private function finalizeConfig(array &$config)
    {
        $this->completeExpressionConfig($config['data_source_expressions']);
        $this->resolveServiceLocatorConfig($config);
    }

    private function completeExpressionConfig(array &$expressionConfig) : void
    {
        foreach ($expressionConfig['keywords'] as $key => $item) {
            $expressionConfig['keywords'][$key . '_size'] = strlen($item);
        }

        foreach ($expressionConfig['markers'] as $key => $item) {
            $expressionConfig['markers'][$key . '_size'] = strlen($item);
        }
    }

    private function resolveServiceLocatorConfig(array &$config) : void
    {
        $count = 0;

        if (isset($config['psr_container'])) {
            if (null !== $config['psr_container'] && ! $config['psr_container'] instanceof \Psr\Container\ContainerInterface) {
                throw new \LogicException(sprintf(
                    'Cannot configure path "big_hydrator.psr_container".' .
                    'The value set to this path must be an instance of "%s". Given type "%s".',
                    \Psr\Container\ContainerInterface::class,
                    is_object($config) ? get_class($config) : gettype($config)
                ));
            }
            $config['service_locator'] = fn ($serviceKey) => ($config['psr_container'])($serviceKey);
            unset($config['psr_container']);
            $count++;

            return;
        }

        if (isset($config['service_provider'])) {
            if ($count) {
                throw new \LogicException(sprintf(
                    'Cannot configure path "big_hydrator.service_provider" section.' .
                    'You must configure either "big_hydrator.psr_container" or "big_hydrator.service_provider" but not both pathes.'
                ));
            }
            if (null !== $config['service_provider'] && ! is_callable($config['service_provider'])) {
                throw new \LogicException(sprintf(
                    'Cannot configure the key "big_hydrator.service_provider".' .
                    'The value set to this key must be a callable. Given type "%s".',
                    is_object($config) ? get_class($config) : gettype($config)
                ));
            }
            $config['service_locator'] = $config['service_provider'];
            unset($config['service_provider']);
        }
    }
}


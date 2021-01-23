<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ConfigurationProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function class_exists;

class HydratorBuilder
{
    private array $configs = [];

    public function build() : \Kassko\ObjectHydrator\Hydrator
    {
        $config = new \Kassko\ObjectHydrator\Config((new ConfigurationProcessor)->processConfig($this->configs));
        $configValues = $config->getValues();

        $reflectionClassRepository = new \Kassko\ObjectHydrator\ClassMetadata\Repository\ReflectionClass;

        $classMetadataLoader = new \Kassko\ObjectHydrator\ClassMetadataLoader(
            (new \Kassko\ObjectHydrator\LoaderResolver)->addLoader(
                (
                    new \Kassko\ObjectHydrator\ClassMetadataLoader\DoctrineAnnotationLoader(
                        new \Doctrine\Common\Annotations\AnnotationReader
                    )
                )->setConfig(
                    new \Kassko\ObjectHydrator\ClassMetadataConfig($config['class_metadata'])
                )->setReflectionClassRepository($reflectionClassRepository)
            )
        );

        $expressionContext = new \Kassko\ObjectHydrator\ExpressionContext;

        $expressionEvaluator = new \Kassko\ObjectHydrator\ExpressionEvaluator(
            $expressionContext,
            $config->getPartition('data_source_expressions'),
            class_exists(ExpressionLanguage::class, true) ? new ExpressionLanguage : null
        );

        $methodInvoker = new \Kassko\ObjectHydrator\MethodInvoker($expressionEvaluator);
        if (isset($configValues['service_locator'])) {
            $methodInvoker->setServiceLocator($config['service_locator']);
        }

        $modelBuilder = new \Kassko\ObjectHydrator\ModelBuilder(
            new \Kassko\ObjectHydrator\ClassMetadata\Repository\DataSource,
            new \Kassko\ObjectHydrator\ClassMetadata\Repository\Expression,
            new \Kassko\ObjectHydrator\ClassMetadata\Repository\Method,
            $reflectionClassRepository,
            $methodInvoker
        );

        $modelLoader = new \Kassko\ObjectHydrator\ModelLoader(
            $classMetadataLoader,
            $modelBuilder
        );

        $dataFetcher = new \Kassko\ObjectHydrator\DataFetcher($methodInvoker);

        $propertyCandidatesResolver = new \Kassko\ObjectHydrator\PropertyCandidatesResolver($expressionEvaluator, $methodInvoker);

        $memberAccessStrategyFactory = new \Kassko\ObjectHydrator\MemberAccessStrategyFactory;

        $objectLoadabilityChecker = new \Kassko\ObjectHydrator\ObjectLoadabilityChecker;

        $hydrator = (new \Kassko\ObjectHydrator\Hydrator(
            $modelLoader,
            $memberAccessStrategyFactory,
            new \Kassko\ObjectHydrator\IdentityMap($objectLoadabilityChecker),
            $objectLoadabilityChecker,
            $dataFetcher,
            $methodInvoker,
            $expressionContext,
            (new \Kassko\ObjectHydrator\EssentialDataProvider(
                $dataFetcher,
                $memberAccessStrategyFactory,
                $configValues['service_locator'] ? $config['service_locator'] : null
            ))->setPropertyCandidatesResolver($propertyCandidatesResolver),
            $expressionEvaluator,
            $config
        ))->setPropertyCandidatesResolver($propertyCandidatesResolver);

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
}


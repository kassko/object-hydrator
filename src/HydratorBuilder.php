<?php

namespace Kassko\ObjectHydrator;

use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use function class_exists;

class HydratorBuilder
{
    protected array $configs = [];

    public function build() : \Kassko\ObjectHydrator\Hydrator
    {
        $config = new \Kassko\ObjectHydrator\Config\Config(
            (new \Kassko\ObjectHydrator\Config\ConfigurationProcessor)->processConfig($this->configs)
        );
        $configValues = $config->getValues();

        $reflectionClassRepository = new \Kassko\ObjectHydrator\Model\Repository\ReflectionClass;

        $leafLoaderConfigurator = new \Kassko\ObjectHydrator\ClassMetadata\Loader\LeafLoaderConfigurator(
            new \Kassko\ObjectHydrator\ClassMetadata\Loader\ClassMetadataConfig($config['class_metadata']),
            $reflectionClassRepository
        );

        $loaders = [];

        $loader = new \Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf\DoctrineAnnotationLoader(
            new \Doctrine\Common\Annotations\AnnotationReader
        );
        $leafLoaderConfigurator->configure($loader);
        $loaders[] = $loader;

        $loader = new \Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf\InnerPhpLoader;
        $leafLoaderConfigurator->configure($loader);
        $loaders[] = $loader;

        $loader = new \Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf\InnerYamlLoader;
        $leafLoaderConfigurator->configure($loader);
        $loaders[] = $loader;

        $loader = new \Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf\PhpFileLoader;
        $leafLoaderConfigurator->configure($loader);
        $loaders[] = $loader;

        $loader = new \Kassko\ObjectHydrator\ClassMetadata\Loader\Leaf\YamlFileLoader;
        $leafLoaderConfigurator->configure($loader);
        $loaders[] = $loader;

        $classMetadataLoader = new \Kassko\ObjectHydrator\ClassMetadata\Loader\ClassMetadataLoader(
            new \Kassko\ObjectHydrator\ClassMetadata\Loader\LoaderResolver($loaders)
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

        $modelBuilder = new \Kassko\ObjectHydrator\ClassMetadata\Loader\ModelBuilder(
            new \Kassko\ObjectHydrator\Model\Repository\DataSource,
            new \Kassko\ObjectHydrator\Model\Repository\Expression,
            new \Kassko\ObjectHydrator\Model\Repository\Method,
            $reflectionClassRepository,
            $methodInvoker
        );

        $modelLoader = new \Kassko\ObjectHydrator\ClassMetadata\Loader\ModelLoader(
            $classMetadataLoader,
            $modelBuilder
        );

        $hydratorProcessingObserverManager = new \Kassko\ObjectHydrator\Observer\HydratorProcessingObserverManager($methodInvoker);

        $dataFetcher = new \Kassko\ObjectHydrator\DataFetcher($methodInvoker, $hydratorProcessingObserverManager);

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
            $config,
            $hydratorProcessingObserverManager
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
        (new RegistryInitializer)->initialize(
            $hydrator,
            $logger ?? new \Psr\Log\NullLogger
        );
    }
}


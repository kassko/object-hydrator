<?php

namespace Big\Hydrator;

use Big\StandardClassMetadata as StdClassMetadata;
use Psr\Container\ContainerInterface;

use function method_exists;

class MethodInvoker
{
    private ExpressionEvaluator $expressionEvaluator;
    private ?\Closure $serviceLocator = null;
    private ?CachePrototype $cachePrototype = null;


    public function __construct(ExpressionEvaluator $expressionEvaluator)
    {
        $this->expressionEvaluator = $expressionEvaluator;
    }

    public function setServiceLocator(\Closure $serviceLocator) : self
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    public function setCachePrototype(CachePrototype $cachePrototype) : self
    {
        $this->cachePrototype = $cachePrototype;

        return $this;
    }

    public function invokeMethod(?StdClassMetadata\Method $method, array $args = [])
    {
        if (null === $method) {
            return null;
        }

        $data = null;

        if (0 === count($args)) {
            $args = $method->getArgs();
        }

        $args = $this->expressionEvaluator->resolveExpressions($args ?: $method->getArgs());

        if ($method->isStatic()) {
            $class = $method->getClass();
            $methodName = $method->getName();

            if (! $this->isInvocable($class, $methodName, $method->isMagicCallAllowed())) {
                throw new \BadMethodCallException(sprintf('Cannot invoke static method with class: %s and method: %s', $class, $methodName));
            }

            $data = $this->invokeStaticMethodWith($class, $methodName, $args, null /*$this->getCache()*/);
        } else {
            if ($method->isInvokerAService()) {
                try {
                    $object = ($this->serviceLocator)($method->getServiceKey());
                } catch (ContainerExceptionInterface $e) {
                    throw new RuntimeException(
                        sprintf(
                            'Cannot invoke method with service: %s and method: %s. '
                            . 'The following error occured when trying to get the service: [%s] - [%s]',
                            get_class($object),
                            $method->getName(),
                            get_class($e),
                            $e->getMessage()
                        )
                    );
                }
            } else {
                $class = $method->getClass();
                $object = new $class;
            }

            $methodName = $method->getName();
            if (! $this->isInvocable($object, $methodName, $method->isMagicCallAllowed())) {
                throw new \BadMethodCallException(sprintf('Cannot invoke method with object: %s and method: %s', get_class($object), $methodName));
            }

            $data = $this->invokeMethodWith($object, $methodName, $args, null);
        }

        return $data;
    }

    public function invokeVisitorsCallbacks(?StdClassMetadata\Methods $methods, ?object $event = null) : ?object
    {
        if (null === $methods) {
            return $event;
        }

        foreach ($methods->items as $method) {
            $this->invokeVisitorCallback($method, $event);
        }

        return $event;
    }

    public function invokeVisitorCallback(?StdClassMetadata\Method $method, ?object $event = null) : ?object
    {
        if (null === $method) {
            return $event;
        }

        $data = null;

        if ($method->isStatic()) {
            $class = $method->getClass();
            $methodName = $method->getName();

            if (! $this->isInvocable($class, $methodName, $method->isMagicCallAllowed())) {
                throw new \BadMethodCallException(sprintf('Cannot invoke static method with class: %s and method: %s', $class, $methodName));
            }

            $args = $this->invokeStaticMethodWith($class, $methodName, $event ? [$event] : [], $this->getCache());
        } else {
            if ($method->isInvokerAService()) {
                try {
                    $object = $this->container->get($method->getServiceId());
                } catch (ContainerExceptionInterface $e) {
                    throw new RuntimeException(
                        sprintf(
                            'Cannot invoke method with service: %s and method: %s. '
                            . 'The following error occured when trying to get the service: [%s] - [%s]',
                            get_class($object),
                            $method->getName(),
                            get_class($e),
                            $e->getMessage()
                        )
                    );
                }
            } else {
                $class = $method->getClass();
                $object = new $class;
            }

            $methodName = $method->getName();
            if (! $this->isInvocable($object, $methodName, $method->isMagicCallAllowed())) {
                throw new \BadMethodCallException(sprintf('Cannot invoke method with object: %s and method: %s', get_class($object), $methodName));
            }

            $args = $this->invokeMethodWith($object, $methodName, $event ? [$event] : [], null);
        }

        return $event;
    }

    private function getCache(string $propertyClass, ?string $objectClass, string $propertyName)
    {
        return $this->cachePrototype ?
        $this->cachePrototype->setKey($this->computeCacheKey($propertyClass, $objectClass, $propertyName))->derive()
        : null;
    }

    private function computeCacheKey(string $propertyClass, ?string $objectClass, string $propertyName)
    {
        if (null === $objectClass) {
            return $propertyClass;
        }

        return $propertyClass . $objectClass . $propertyName;
    }

    private function invokeMethodWith(object $object, string $methodName, array $args, ?CachePrototype $cachePrototype = null)
    {
        if (null === $cachePrototype) {
            return $object->$methodName(...$args);
        }

        return $cachePrototype->execute(function () use ($object, $methodName, $args) {
            return $object->$methodName(...$args);
        });
    }

    private function invokeStaticMethodWith(string $class, string $methodName, array $args, ?CachePrototype $cachePrototype = null)
    {
        if (null === $cachePrototype) {
            return $class::$methodName(...$args);
        }

        return $cachePrototype->execute(function () use ($class, $methodName, $args) {
            return $class::$methodName(...$args);
        });
    }

    private function isInvocable($object, $methodName, $magicCallAllowed) : bool
    {
        if ($magicCallAllowed) {
            return method_exists($object, '__call') || (method_exists($object, $methodName) && is_callable([$object, $methodName]));
        }

        return method_exists($object, $methodName) && is_callable([$object, $methodName]);
    }
}

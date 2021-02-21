<?php

namespace Kassko\ObjectHydrator\ClassMetadata\Loader;

use function get_class;

final class ClassMetadataConfig
{
    private const PATH_DELIMITER = '.';

    /**
     * ie:
     * [
     *      '\\' => [
     *          'annotations' => [
     *              'type' => ['doctrine']
     *          ],
     *      ]
     * ]
     */
    private array $values;

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function getValueByObject(object $object)
    {
        return $this->getValueByNamespace(get_class($object));
    }

    public function getValueByNamespace(string $namespace)
    {
        $configNamespaceUsed = '_defaults';

        foreach ($this->values as $configNamespace => $configContent) {
            if (0 === strpos($namespace, $configNamespace)) {
                $configNamespaceUsed = $configNamespace;
                break;
            }
        }

        return $this->getValueByPath($configNamespaceUsed);
    }

    public function getValueByPathAndObject(string $path, object $object)
    {
        return $this->getValueByPathAndNamespace($path, get_class($object));
    }

    public function getValueByPathAndNamespace(string $path, string $namespace)
    {
        $configNamespaceUsed = '_defaults';

        foreach ($this->values as $configNamespace => $configContent) {
            if (0 === strpos($namespace, $configNamespace)) {
                $configNamespaceUsed = $configNamespace;
                break;
            }
        }

        return $this->getValueByPath($configNamespaceUsed . self::PATH_DELIMITER . $path);
    }

    private function getValueByPath(string $path)
    {
        $pathParts = explode(self::PATH_DELIMITER, $path);

        $previousValuesPartition = [];
        $valuesPartition = $this->values;
        $pathCursor = '';
        foreach ($pathParts as $pathPart) {
            $pathCursor .= $pathCursor ? self::PATH_DELIMITER . $pathPart : $pathPart;

            if (! isset($valuesPartition[$pathPart])) {
                $message = sprintf(
                    'Cannot get a value for the path "%s" from class metadata config.',
                    $path,
                );

                if (count($previousValuesPartition)) {
                    $availableKeys = implode(',', array_keys($previousValuesPartition));
                    $message .= sprintf(' Available keys in path "%s" are "[%s]".', $pathCursor, $availableKeys);
                }

                throw new \LogicException($message);
            }

            $previousValuesPartition = $valuesPartition;
            $valuesPartition = $valuesPartition[$pathPart];
        }

        return $valuesPartition;
    }
}

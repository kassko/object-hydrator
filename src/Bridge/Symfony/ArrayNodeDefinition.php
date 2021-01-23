<?php

namespace Kassko\ObjectHydrator\Bridge\Symfony;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition as SfArrayNodeDefinition;

class ArrayNodeDefinition extends SfArrayNodeDefinition
{
    /**
     * @return ArrayNodeDefinition
     */
    public function bigArrayPrototype()
    {
        return $this->prototype('big_array');
    }

    /**
     * @return NodeDefinition[]
     */
    public function getChildNodeDefinitions()
    {
        return $this->children;
    }

    /**
     * Finds a node defined by the given $nodePath.
     *
     * @param string $nodePath The path of the node to find. e.g "doctrine.orm.mappings"
     */
    public function find(string $nodePath): NodeDefinition
    {
        $firstPathSegment = (false === $pathSeparatorPos = strpos($nodePath, $this->pathSeparator))
            ? $nodePath
            : substr($nodePath, 0, $pathSeparatorPos);

        if (null === $node = ($this->children[$firstPathSegment] ?? null)) {
            throw new \RuntimeException(sprintf('Node with name "%s" does not exist in the current node "%s".', $firstPathSegment, $this->name));
        }

        if (false === $pathSeparatorPos) {
            return $node;
        }

        return $node->find(substr($nodePath, $pathSeparatorPos + \strlen($this->pathSeparator)));
    }
}

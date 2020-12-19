<?php

namespace Big\Hydrator\Bridge\Symfony;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition as SfArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder as BaseNodeBuilder;

use function method_exists;

class NodeBuilder extends BaseNodeBuilder
{
    public function init() : self
    {
        $this->nodeMapping['array'] =
            method_exists(SfArrayNodeDefinition::class, 'find') ? (SfArrayNodeDefinition::class) : (ArrayNodeDefinition::class);

        return $this;
    }

    /**
     * Creates a child array node.
     *
     * @param string $name The name of the node
     *
     * @return ArrayNodeDefinition The child node
     */
    public function bigArrayNode($name)
    {
        return $this->node($name, 'big_array');
    }
}

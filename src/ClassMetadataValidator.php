<?php

namespace Big\Hydrator;

use Big\Hydrator\Bridge\Symfony\NodeBuilder;
use Big\Hydrator\ClassMetadata\Model;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use function is_object;
use function get_class;
use function gettype;
use function is_callable;
use function method_exists;
use function strlen;
use function version_compare;

/**
    class:
        default_autoconfigure_properties: true
        raw_data_key_style: 'underscore'
        to_raw_data_key_style_converter:
            class: 'this'
            method: 'meth'
        dynamic_attribute_marker:
    data_sources: ~
    expressions: ~
    methods: ~
    properties:
        prop_a:
            single_type:
                type:  ~
                class: ~
                key_in_raw_data: ~
                data_source: ~
                data_source_ref: ~
                discriminator: ~
                discriminator_method_ref: ~
                discriminator_expression_ref: ~
                loading: ~
                default_value: ~
                getter: ~
                setter: ~
                dynamic_attribute_marker: ~
                variables: ~
            collection_type:
                items_class: ~
        prop_b:
            single_type: ~
            collection_type:
                type: ~
                class: ~
                keyInRawData: ~
                data_source: ~
                data_source_ref: ~
                discriminator_expression_ref: ~
                discriminator_method_ref: ~
                loading: ~
                default_value: ~
                getter: ~
                setter: ~
                dynamic_attribute_marker: ~
                variables: ~
                adder: ~
                items_class: ~
                items_class_candidates:
                    - { class: ~, discriminator: ~, discriminator_expression_ref: ~, discriminator_method_ref: ~ }
 */
class ClassMetadataValidator implements ConfigurationInterface
{
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $rootNode = $this->getRootNode('class_metadata', $treeBuilder);

        $rootNode
            //->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('class')
                    ->treatTrueLike(['enabled' => true])
                    ->treatFalseLike(['enabled' => false])
                    ->treatNullLike(['enabled' => true])
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->booleanNode('default_autoconfigure_properties')->defaultTrue()->end()
                        ->enumNode('raw_data_key_style')->values(['underscore', 'dash', 'camel_case', 'custom'])->defaultValue('underscore')->end()
                        ->append($this->methodNode('to_raw_data_key_style_converter', false))
                        ->scalarNode('default_adder_name_format')->end()
                        ->scalarNode('dynamic_attribute_marker')->defaultValue('_')->end()
                    ->end()
                ->end()

                ->append($this->methodNode())

                ->arrayNode('methods')
                    ->arrayPrototype()
                        ->treatTrueLike(['enabled' => true])
                        ->treatFalseLike(['enabled' => false])
                        ->treatNullLike(['enabled' => true])
                        //->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enabled')->defaultTrue()->end()
                            ->scalarNode('id')->defaultNull()->end()
                            ->scalarNode('class')->defaultNull()->end()
                            ->scalarNode('service_key')->defaultNull()->end()
                            ->scalarNode('name')->defaultNull()->end()
                            ->arrayNode('args')
                                ->scalarPrototype()->end()
                            ->end()
                            ->scalarNode('magic_call_allowed')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()

                ->append($this->expressionNode())

                ->arrayNode('expressions')
                    ->arrayPrototype()
                        ->treatTrueLike(['enabled' => true])
                        ->treatFalseLike(['enabled' => false])
                        ->treatNullLike(['enabled' => true])
                        //->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enabled')->defaultTrue()->end()
                            ->scalarNode('id')->defaultNull()->end()
                            ->scalarNode('value')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()

                ->append($this->dataSourceNode())

                ->arrayNode('data_sources')
                    ->arrayPrototype()
                        ->treatTrueLike(['enabled' => true])
                        ->treatFalseLike(['enabled' => false])
                        ->treatNullLike(['enabled' => true])
                        //->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enabled')->defaultTrue()->end()
                            ->scalarNode('id')->defaultNull()->end()
                            ->append($this->methodNode())
                            ->enumNode('loading_mode')
                                ->values([
                                    Model\DataSource::LOADING_MODE_LAZY,
                                    Model\DataSource::LOADING_MODE_EAGER,
                                ])
                                ->defaultValue(Model\DataSource::LOADING_MODE_LAZY)
                            ->end()
                            ->scalarNode('indexed_by_properties_keys')->defaultNull()->end()
                            ->enumNode('loading_scope')
                                ->values([
                                    Model\DataSource::LOADING_SCOPE_DATA_SOURCE,
                                    Model\DataSource::LOADING_SCOPE_PROPERTY,
                                    Model\DataSource::LOADING_SCOPE_DATA_SOURCE_ONLY_KEYS,
                                    Model\DataSource::LOADING_SCOPE_DATA_SOURCE_EXCEPT_KEYS
                                ])
                                ->defaultValue(Model\DataSource::LOADING_SCOPE_DATA_SOURCE)
                            ->end()
                            ->arrayNode('loading_scope_keys')
                                ->scalarPrototype()->end()
                            ->end()
                            ->scalarNode('fallback_data_source_ref')->defaultNull()->end()
                            //->append($this->dataSourceNode('fallback_data_source'))->end()
                            ->append($this->methodsNode('after_metadata_loading'))
                            ->append($this->methodsNode('before_data_fetching'))
                            ->append($this->methodsNode('after_data_fetching'))
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('not_to_autoconfigure_properties')
                    ->scalarPrototype()->end()
                ->end()

                ->arrayNode('properties')
                    ->arrayPrototype()
                    ->children()
                        ->append($this->basicPropertyNode())
                        ->append($this->collectionPropertyNode())
                        ->append($this->candidatesPropertyNode())
                    ->end()
                ->end()
            ->end();

            //$this->find('class_metadata.properties.firstName.basic')->append($this->methodNodeByNode());

        return $treeBuilder;
    }

    /*private function basePropertyNode(string $nodeName = 'attributes') : ArrayNodeDefinition
    {
        $node = $this->getRootNode($nodeName);

        $node
            //->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('type')->defaultNull()->end()
                ->scalarNode('class')->defaultNull()->end()
                ->scalarNode('key_in_raw_data')->defaultNull()->end()
                ->append($this->dataSourceNode())
                ->scalarNode('data_source_ref')->defaultNull()->end()
                ->append($this->methodNode('discriminator_method'))
                ->append($this->expressionNode('discriminator_expression'))
                ->scalarNode('discriminator_method_ref')->defaultNull()->end()
                ->scalarNode('discriminator_expression_ref')->defaultNull()->end()
                //->enumNode('loading')->values(['lazy', 'eager'])->defaultValue('lazy')->end()
                ->scalarNode('default_value')->defaultNull()->end()
                ->scalarNode('getter')->defaultNull()->end()
                ->scalarNode('setter')->defaultNull()->end()
                ->scalarNode('dynamic_attribute_marker')->defaultNull()->end()
                ->arrayNode('variables')->scalarPrototype()->end()
                ->append($this->callbackNode('callbacks_using_metadata'))
                ->append($this->callbackNode('callbacks_hydration'))
                ->append($this->callbackNode('callbacks_assigning_hydrated_value'))
            ->end();

        return $node;
    }*/

    private function basicPropertyNode() : ArrayNodeDefinition
    {
        $node = $this->getRootNode('single_type');

        $node
            ->treatTrueLike(['enabled' => true])
            ->treatFalseLike(['enabled' => false])
            ->treatNullLike(['enabled' => true])
            //->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('property_kind')->defaultValue('single_type')->end()

                //Bases nodes.
                ->scalarNode('type')->defaultNull()->end()
                ->scalarNode('class')->defaultNull()->end()
                ->scalarNode('key_in_raw_data')->defaultNull()->end()
                ->append($this->dataSourceNode())
                ->scalarNode('data_source_ref')->defaultNull()->end()
                ->append($this->methodNode('discriminator_method'))
                ->append($this->expressionNode('discriminator_expression'))
                ->scalarNode('discriminator_method_ref')->defaultNull()->end()
                ->scalarNode('discriminator_expression_ref')->defaultNull()->end()
                //->enumNode('loading')->values(['lazy', 'eager'])->defaultValue('lazy')->end()
                ->scalarNode('default_value')->defaultNull()->end()
                ->scalarNode('getter')->defaultNull()->end()
                ->scalarNode('setter')->defaultNull()->end()
                ->scalarNode('dynamic_attribute_marker')->defaultNull()->end()
                ->arrayNode('variables')->scalarPrototype()->end()
            /*    ->append($this->callbackNode('callbacks_using_metadata'))
                ->append($this->callbackNode('callbacks_hydration'))
                ->append($this->callbackNode('callbacks_assigning_hydrated_value'))*/
            ->end();

        return $node;
    }

    private function collectionPropertyNode() : ArrayNodeDefinition
    {
        $node = $this->getRootNode('collection_type');

        $node
            ->treatTrueLike(['enabled' => true])
            ->treatFalseLike(['enabled' => false])
            ->treatNullLike(['enabled' => true])
            //->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('property_kind')->defaultValue('collection_type')->end()
                ->scalarNode('adder')->defaultNull()->end()
                ->scalarNode('items_class')->defaultNull()->end()
                ->arrayNode('item_class_candidate')
                    ->children()
                        ->scalarNode('class')->defaultNull()->end()
                        ->append($this->methodNode('discriminator_method'))
                        ->append($this->expressionNode('discriminator_expression'))
                    ->end()
                ->end()
                ->arrayNode('item_class_candidates')
                    ->arrayPrototype()
                    ->children()
                        ->scalarNode('class')->defaultNull()->end()
                        ->append($this->methodNode('discriminator_method'))
                        ->append($this->expressionNode('discriminator_expression'))
                        ->end()
                    ->end()
                ->end()

                //Bases nodes
                ->scalarNode('type')->defaultNull()->end()
                ->scalarNode('class')->defaultNull()->end()
                ->scalarNode('key_in_raw_data')->defaultNull()->end()
                ->append($this->dataSourceNode())
                ->scalarNode('data_source_ref')->defaultNull()->end()
                ->append($this->methodNode('discriminator_method'))
                ->append($this->expressionNode('discriminator_expression'))
                ->scalarNode('discriminator_method_ref')->defaultNull()->end()
                ->scalarNode('discriminator_expression_ref')->defaultNull()->end()
                //->enumNode('loading')->values(['lazy', 'eager'])->defaultValue('lazy')->end()
                ->scalarNode('default_value')->defaultNull()->end()
                ->scalarNode('getter')->defaultNull()->end()
                ->scalarNode('setter')->defaultNull()->end()
                ->scalarNode('dynamic_attribute_marker')->defaultNull()->end()
                ->arrayNode('variables')->scalarPrototype()->end()
            /*    ->append($this->callbackNode('callbacks_using_metadata'))
                ->append($this->callbackNode('callbacks_hydration'))
                ->append($this->callbackNode('callbacks_assigning_hydrated_value'))*/
            ->end();

        return $node;
    }

    private function candidatesPropertyNode() : ArrayNodeDefinition
    {
        $node = $this->getRootNode('candidates');

        $node
            ->treatTrueLike(['enabled' => true])
            ->treatFalseLike(['enabled' => false])
            ->treatNullLike(['enabled' => true])
            //->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('property_kind')->defaultValue('candidates')->end()
                ->arrayNode('candidates')
                    ->arrayPrototype()
                    ->children()
                        ->append($this->basicPropertyNode())
                        ->append($this->collectionPropertyNode())
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('variables')
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $node;
    }

    private function dataSourceNode(string $nodeName = 'data_source') : ArrayNodeDefinition
    {
        $node = $this->getRootNode($nodeName);

        $node
            ->treatTrueLike(['enabled' => true])
            ->treatFalseLike(['enabled' => false])
            ->treatNullLike(['enabled' => true])
            //->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('id')->defaultNull()->end()
                ->append($this->methodNode())
                ->enumNode('loading_mode')
                    ->values([
                        Model\DataSource::LOADING_MODE_LAZY,
                        Model\DataSource::LOADING_MODE_EAGER,
                    ])
                    ->defaultValue(Model\DataSource::LOADING_MODE_LAZY)
                ->end()
                ->scalarNode('indexed_by_properties_keys')->defaultNull()->end()
                ->enumNode('loading_scope')
                    ->values([
                        Model\DataSource::LOADING_SCOPE_DATA_SOURCE,
                        Model\DataSource::LOADING_SCOPE_PROPERTY,
                        Model\DataSource::LOADING_SCOPE_DATA_SOURCE_ONLY_KEYS,
                        Model\DataSource::LOADING_SCOPE_DATA_SOURCE_EXCEPT_KEYS
                    ])
                    ->defaultValue(Model\DataSource::LOADING_SCOPE_DATA_SOURCE)
                ->end()
                ->arrayNode('loading_scope_keys')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('fallback_data_source_ref')->defaultNull()->end()
                //->append($this->dataSourceNode('fallback_data_source'))->end()
                ->append($this->methodsNode('after_metadata_loading'))
                ->append($this->methodsNode('before_data_fetching'))
                ->append($this->methodsNode('after_data_fetching'))
            ->end();

        return $node;
    }

    private function methodsNode(string $nodeName = 'methods') : ArrayNodeDefinition
    {
        $node = $this->getRootNode($nodeName);

        $node
            ->arrayPrototype()
            ->append($this->methodNode())
            ->end();

        return $node;
    }

    private function methodNode(string $nodeName = 'method', bool $addDefaultsIfNotSet = true) : ArrayNodeDefinition
    {
        $node = $this->getRootNode($nodeName);

        $node
            ->treatTrueLike(['enabled' => true])
            ->treatFalseLike(['enabled' => false])
            ->treatNullLike(['enabled' => true])
            //->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('id')->defaultNull()->end()
                ->scalarNode('class')->defaultNull()->end()
                ->scalarNode('service_key')->defaultNull()->end()
                ->scalarNode('name')->defaultNull()->end()
                ->arrayNode('args')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('magic_call_allowed')->defaultNull()->end()
            ->end();


        return $node;
    }

    private function methodNodeByNode(ArrayNodeDefinition $node, $addDefaultsIfNotSet = true) : ArrayNodeDefinition
    {
        /*if ($addDefaultsIfNotSet) {
            $node->addDefaultsIfNotSet();
        }*/


        return $node;
    }

    private function expressionNode(string $nodeName = 'expression') : ArrayNodeDefinition
    {
        $node = $this->getRootNode($nodeName);

        $node
            ->treatTrueLike(['enabled' => true])
            ->treatFalseLike(['enabled' => false])
            ->treatNullLike(['enabled' => true])
            //->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('id')->defaultNull()->end()
                ->scalarNode('value')->defaultNull()->end()
            ->end();

        return $node;
    }

    private function callbackNode(string $nodeName) : ArrayNodeDefinition
    {
        $node = $this->getRootNode($nodeName);

        $node
            ->treatTrueLike(['enabled' => true])
            ->treatFalseLike(['enabled' => false])
            ->treatNullLike(['enabled' => true])
            //->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->append($this->methodNode('before'))
                ->append($this->methodNode('after'))
                ->append($this->methodNode('before_collection'))
                ->append($this->methodNode('after_collection'))
            ->end();

        return $node;
    }

    private function getRootNode(string $nodeName, TreeBuilder &$treeBuilder = null)
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder($nodeName, 'array', (new NodeBuilder())->init());
            $node = $treeBuilder->getRootNode();
        } else {//Keep compatibility with Symfony <= 4.3
            /**
             * @see https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Config/Definition/Builder/TreeBuilder.php#L48
             */
            $treeBuilder = new TreeBuilder;
            $node = $treeBuilder->root($nodeName, 'array', (new NodeBuilder())->init());
        }

        return $node;
    }

    /*private function discriminatorNode(string $nodeName = 'discriminator') : ArrayNodeDefinition
    {
        $node = $this->getRootNode($nodeName);

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->expressionNode())
                ->append($this->methodNode())
            ->end();

        return $node;
    }*/
}

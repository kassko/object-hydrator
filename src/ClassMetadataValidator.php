<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\Bridge\Symfony\NodeBuilder;
use Kassko\ObjectHydrator\ClassMetadata\Model;
use Kassko\ObjectHydrator\ClassMetadata\Model\Enum;
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
        raw_data_key_style_converter:
            class: 'this'
            method: 'meth'
        dynamic_attribute_marker:
        accessors_to_bypass: false
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
                factory_method_name: ~
                constructor_method_args_to_hydrate: false
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
                factory_method_name
                constructor_method_args_to_hydrate: false
                default_value: ~
                getter: ~
                setter: ~
                dynamic_attribute_marker: ~
                variables: ~
                adder: ~
                items_class: ~
                items_class_candidates:
                    -
                      class: ~
                      factory_method_name: ~
                      constructor_method_args_to_hydrate: false
                      discriminator: ~
                      discriminator_expression_ref: ~
                      discriminator_method_ref: ~
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
                        ->enumNode('raw_data_key_style')
                            ->values([
                                Enum\RawDataKeyStyle::UNDERSCORE,
                                Enum\RawDataKeyStyle::DASH,
                                Enum\RawDataKeyStyle::CAMEL_CASE,
                                Enum\RawDataKeyStyle::CUSTOM
                            ])
                            ->defaultValue(Enum\RawDataKeyStyle::UNDERSCORE)
                        ->end()
                        ->append($this->methodNode('raw_data_key_style_converter', false))
                        ->scalarNode('default_adder_name_format')->defaultValue('add%sItem')->end()
                        ->booleanNode('accessors_to_bypass')->defaultFalse()->end()
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
                                    Enum\DataSourceLoadingMode::LAZY,
                                    Enum\DataSourceLoadingMode::EAGER,
                                ])
                                ->defaultValue(Enum\DataSourceLoadingMode::LAZY)
                            ->end()
                            ->booleanNode('indexed_by_properties_keys')->defaultTrue()->end()
                            ->enumNode('loading_scope')
                                ->values([
                                    Enum\DataSourceLoadingScope::DATA_SOURCE,
                                    Enum\DataSourceLoadingScope::PROPERTY,
                                    Enum\DataSourceLoadingScope::DATA_SOURCE_ONLY_KEYS,
                                    Enum\DataSourceLoadingScope::DATA_SOURCE_EXCEPT_KEYS
                                ])
                                ->defaultValue(Enum\DataSourceLoadingScope::DATA_SOURCE)
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
                ->append($this->rawDataLocationNode())
                ->append($this->instanceCreationNode())
                ->scalarNode('default_value')->defaultNull()->end()
                ->scalarNode('getter')->defaultNull()->end()
                ->scalarNode('setter')->defaultNull()->end()
                ->scalarNode('dynamic_attribute_marker')->defaultNull()->end()
                ->arrayNode('variables')->scalarPrototype()->end()->end()
                ->arrayNode('dynamic_attributes')->scalarPrototype()->end()->end()
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
                        ->append($this->rawDataLocationNode())
                        ->append($this->instanceCreationNode())
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
                ->append($this->rawDataLocationNode())
                ->append($this->instanceCreationNode())
                ->scalarNode('default_value')->defaultNull()->end()
                ->scalarNode('getter')->defaultNull()->end()
                ->scalarNode('setter')->defaultNull()->end()
                ->arrayNode('variables')->scalarPrototype()->end()->end()
                ->scalarNode('dynamic_attribute_marker')->defaultNull()->end()
                ->arrayNode('dynamic_attributes')->scalarPrototype()->end()->end()
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
                        Enum\DataSourceLoadingMode::LAZY,
                        Enum\DataSourceLoadingMode::EAGER,
                    ])
                    ->defaultValue(Enum\DataSourceLoadingMode::LAZY)
                ->end()
                ->booleanNode('indexed_by_properties_keys')->defaultTrue()->end()
                ->enumNode('loading_scope')
                    ->values([
                        Enum\DataSourceLoadingScope::DATA_SOURCE,
                        Enum\DataSourceLoadingScope::PROPERTY,
                        Enum\DataSourceLoadingScope::DATA_SOURCE_ONLY_KEYS,
                        Enum\DataSourceLoadingScope::DATA_SOURCE_EXCEPT_KEYS
                    ])
                    ->defaultValue(Enum\DataSourceLoadingScope::DATA_SOURCE)
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

    private function methodNode(string $nodeName = 'method') : ArrayNodeDefinition
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

    private function rawDataLocationNode() : ArrayNodeDefinition
    {
        $node = $this->getRootNode('raw_data_location');

        $node
            ->treatTrueLike(['enabled' => true])
            ->treatFalseLike(['enabled' => false])
            ->treatNullLike(['enabled' => true])
            //->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->enumNode('location_name')
                    ->values([Enum\RawDataLocation::PARENT_])
                    ->defaultValue(Enum\RawDataLocation::PARENT_)
                ->end()
                ->arrayNode('keys_mapping_values')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('keys_mapping_prefix')->defaultNull()->end()
                ->append($this->methodNode('keys_mapping_method'))->end()
            ->end();

        return $node;
    }

    private function instanceCreationNode() : ArrayNodeDefinition
    {
        $node = $this->getRootNode('instance_creation');

        $node
            ->treatTrueLike(['enabled' => true])
            ->treatFalseLike(['enabled' => false])
            ->treatNullLike(['enabled' => true])
            //->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('factory_method_name')->defaultNull()->end()
                ->append($this->methodNode('factory_method'))
                ->booleanNode('set_properties_through_creation_method_when_possible')->defaultFalse()->end()
                ->booleanNode('always_access_properties_directly')->defaultFalse()->end()
                ->append($this->methodNode('after_construction_method'))
                ->append($this->methodsNode('after_construction_methods'))
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

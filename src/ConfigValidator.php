<?php

namespace Big\Hydrator;

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

class ConfigValidator implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $builder = new TreeBuilder('big_hydrator');
            $rootNode = $builder->getRootNode();
        } else {//Keep compatibility with Symfony <= 4.3
            /**
             * @see https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Config/Definition/Builder/TreeBuilder.php#L48
             */
            $builder = new TreeBuilder;
            $rootNode = $builder->root('big_hydrator');
        }

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('class_metadata')
                    ->useAttributeAsKey('namespace')
                    ->defaultValue(['\\' => ['annotations' => ['type' => $this->getPreferedAnnotationsKind()]]])
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('annotations')
                                ->canBeEnabled()
                                ->addDefaultsIfNotSet()
                                 ->children()
                                    ->enumNode('type')->values(['native', 'doctrine'])->defaultValue($this->getPreferedAnnotationsKind())->end()
                                ->end()
                            ->end()
                            ->arrayNode('file_resource')
                                ->canBeEnabled()
                                ->children()
                                    ->enumNode('type')->values(['php', 'yaml', 'json'])->isRequired()->end()
                                    ->scalarNode('file_path')->isRequired()->end()
                                ->end()
                            ->end()
                            ->arrayNode('method_ressource')
                                ->canBeEnabled()
                                ->children()
                                    ->enumNode('type')->values(['php', 'yaml', 'json'])->isRequired()->end()
                                    ->scalarNode('method_name')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('data_source_expressions')
                    /*->addDefaultsIfNotSet()
                    ->children()
                    ->end()
                    ->append($this->getExpressionSection())*/
                ->end()
                ->variableNode('psr_container')->cannotBeEmpty()->defaultNull()->end()
                ->variableNode('service_provider')->cannotBeEmpty()->defaultNull()->end()
                ->scalarNode('logger_key')->cannotBeEmpty()->defaultNull()->end()
            ->end()
        ;

        $this->addDataSourceExpressionsSection($rootNode->find('data_source_expressions'));

        return $builder;
    }

    private function getPreferedAnnotationsKind() : string
    {
        return $this->preferDoctrineAnnotations() ? 'doctrine' : 'native';
    }

    private function preferDoctrineAnnotations() : bool
    {
        return version_compare(PHP_VERSION, '8.0.0') < 0;
    }

    private function addDataSourceExpressionsSection(ArrayNodeDefinition $node) : void
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('keywords')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('this_keyword')->defaultValue('##this')->end()
                        ->scalarNode('this_norm_keyword')->defaultValue('##thisNorm')->end()
                        ->scalarNode('variables_keyword')->defaultValue('##variables')->end()
                    ->end()
                    ->validate()
                        ->always(function (array $config) {
                            return $this->validateMappingExpressionTokens($config, 'keyword');
                        })
                    ->end()
                ->end()
                ->arrayNode('markers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('property_marker')->defaultValue('#')->end()
                        ->scalarNode('direct_property_marker')->defaultValue('!#')->end()
                        ->scalarNode('service_marker')->defaultValue('@')->end()
                        ->scalarNode('source_marker')->defaultValue('#source_')->end()
                        ->scalarNode('source_tag_marker')->defaultValue('#sourceTag_')->end()
                        ->scalarNode('variable_marker')->defaultValue('#variable_')->end()
                    ->end()
                    ->validate()
                        ->always()
                        ->then(function (array $config) {
                            return $this->validateMappingExpressionTokens($config, 'marker');
                        })
                    ->end()
                ->end()
            ->end()
        ;

        //return $node;
    }

    private function validateMappingExpressionTokens(array $config, string $expressionTokenKind) : array
    {
        foreach ($config as $key => $value) {
            $limitSize = 11;
            $forbiddenExpressionTokens = ['<', '>'];

            $forbiddenExpressionTokenDetected = null;

            foreach ($forbiddenExpressionTokens as $forbiddenExpressionToken) {
                if (false !== strpos($value, $forbiddenExpressionToken)) {
                    $forbiddenExpressionTokenDetected = $forbiddenExpressionToken;
                    break;
                }
            }

            if (null !== $forbiddenExpressionTokenDetected) {
                throw new \LogicException(sprintf(
                    'Cannot change the value of %s in this way. ' .
                    ' Forbidden character detected "[%s]".' .
                    ' The new value must not contain such characters "%s".',
                    $expressionTokenKind,
                    implode(',', $forbiddenExpressionTokens),
                    $forbiddenExpressionTokenDetected
                ));
            }

            if (($size = strlen($value)) > $limitSize) {
                throw new \LogicException(sprintf(
                    'Cannot change the value of %s "%s". The new value must contain at most %d characters, %d given.',
                    "%s",
                    $expressionTokenKind,
                    $key,
                    $limitSize,
                    $size
                ));
            }
        }

        return $config;
    }
}

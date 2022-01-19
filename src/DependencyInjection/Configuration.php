<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function __construct(private string $name = 'commandbus')
    {
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder($this->name);
        $root = $builder->getRootNode();

        $this->addHandlersSection($root);
        $this->addRemoteSection($root);
        $this->addRetriesSection($root);

        return $builder;
    }

    private function addHandlersSection(ArrayNodeDefinition $node): void
    {
        $node
            ->fixXmlConfig('handler')
            ->children()
                ->arrayNode('handlers')
                    ->useAttributeAsKey('name')
                    ->defaultValue([])
                    ->validate()
                        ->ifTrue(static function (array $v) {
                            foreach (array_keys($v) as $key) {
                                if (!class_exists($key)) {
                                    return true;
                                }
                            }

                            return false;
                        })
                        ->thenInvalid('Handlers keys must be known command class name.')
                    ->end()
                    ->scalarPrototype()->cannotBeEmpty()->end()
                ->end()
            ->end()
        ;
    }

    private function addRemoteSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('remote')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('serializer')->defaultValue('native')->end()
                        ->arrayNode('transport')
                            ->fixXmlConfig('connection')
                            ->children()
                                ->scalarNode('default')->defaultNull()->end()
                                ->arrayNode('connections')
                                    ->useAttributeAsKey('name')
                                    ->arrayPrototype()
                                        ->children()
                                            ->scalarNode('dsn')->cannotBeEmpty()->end()
                                            ->arrayNode('options')
                                                ->useAttributeAsKey('name')
                                                ->defaultValue([])
                                                ->variablePrototype()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('routes')
                                    ->useAttributeAsKey('name')
                                    ->defaultValue([])
                                    ->scalarPrototype()->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('consumer')
                            ->fixXmlConfig('queue')
                            ->fixXmlConfig('option')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('queues')
                                    ->useAttributeAsKey('name')
                                    ->arrayPrototype()
                                        ->useAttributeAsKey('name')
                                        ->variablePrototype()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('options')
                                    ->useAttributeAsKey('name')
                                    ->variablePrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('local')
                            ->scalarPrototype()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addRetriesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('retries')
                    ->fixXmlConfig('policy', 'policies')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('default')->defaultNull()->end()
                        ->arrayNode('policies')
                            ->useAttributeAsKey('name')
                            ->validate()
                                ->ifTrue(static function (array $v) {
                                    foreach (array_keys($v) as $key) {
                                        if (!class_exists($key)) {
                                            return true;
                                        }
                                    }

                                    return false;
                                })
                                ->thenInvalid('Policies keys must be known command class name.')
                            ->end()
                            ->scalarPrototype()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}

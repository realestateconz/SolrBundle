<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('realestate_solr');

        $rootNode
            ->children()
            
            ->scalarNode('metadata_cache_driver')->defaultValue('array')->end()
            ->arrayNode('solarium')
             
                ->children()
                    ->scalarNode('default_connection')->end()
                
                
                    ->arrayNode('connection')
                        ->children()
                            
                            ->scalarNode('adapter')->end()
                            
                            ->arrayNode('adapteroptions')
                                ->children()
                                    ->scalarNode('host')->defaultValue('127.0.0.1')->end()
                                    ->scalarNode('port')->defaultValue('8080')->end()
                                    ->scalarNode('path')->defaultValue('/solr')->end()
                                    ->scalarNode('timeout')->defaultValue('10')->end()
                                    ->scalarNode('adapter')->defaultValue('Zend_Http_Client_Adapter_Socket')->end()
                                ->end()
                             ->end()
                
                             ->arrayNode('querytype')
                                ->children()
                                    ->arrayNode('select')
                                        ->children()
                                            ->scalarNode('query')->end()
                                            ->scalarNode('requestbuilder')->end()
                                            ->scalarNode('responseparser')->end()
                                        ->end()
                                     ->end()
                                ->end()
                             ->end()
                            
                        ->end()

                    ->end()
                ->end()
                            
            //->append($this->getSolariumConnectionsNode())
                    
            ->end();

        return $treeBuilder;
    }
    
    
    private function getSolariumConnectionsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('connections');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                
                ->children()
                    ->scalarNode('adapter')->end()
                    ->arrayNode('options')
                        ->useAttributeAsKey('key')
                        ->prototype('scalar')->end()
                    ->end()
                 
                ->end()
                 
            ->end()
        ;

        return $node;
    }
}
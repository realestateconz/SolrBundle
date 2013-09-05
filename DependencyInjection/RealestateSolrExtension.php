<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class RealestateSolrExtension extends Extension
{
    protected $resources = array(
        'solr' => 'solr.xml',
    );

    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        
        $this->loadDefaults($container);
        
        $container->setParameter('solr.solarium.default_connection', isset($config['solarium']['default_connection']) ? $config['solarium']['default_connection'] : 'default');
        
        $connections = array();
        
        if(isset($config['solarium']['connection'])) {
            $connections['default'] = sprintf('solr.solarium.%s_connection', 'default');
        
            $container->setParameter('solr.solarium.connections', $connections);
            
            $this->loadSolariumConnection('default', $config['solarium']['connection'], $container);
            
        }
        
        if(isset($config['metadata_cache_driver'])) {
            //$container->setParameter('solr.metadata_cache_driver.class', $container->getParameter('solr.odm.cache.' . $config['metadata_cache_driver'] . '.class') );
            $container->setParameter('solr.metadata_cache_driver.class', $container->getParameter('doctrine.orm.cache.' . $config['metadata_cache_driver'] . '.class') );
        } else {
            $container->setParameter('solr.metadata_cache_driver.class', $container->getParameter('solr.odm.cache.array.class') );
        }
    }
    
    
    /**
     * Loads a configured Solarium connection.
     *
     * @param string           $name       The name of the connection
     * @param array            $connection Connection configuration options.
     * @param ContainerBuilder $container  A ContainerBuilder instance
     */
    protected function loadSolariumConnection($name, array $settings, ContainerBuilder $container)
    {
        $container
            ->setDefinition(sprintf('solr.solarium.%s_connection', $name), new DefinitionDecorator('solr.solarium.connection'))
            ->setArguments(
                    array($settings)
            )
        ;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__ . '/../Resources/config/schema';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/realestate_solr';
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadDefaults($container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach ($this->resources as $resource) {
            $loader->load($resource);
        }
    }
}

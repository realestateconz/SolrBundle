<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */


namespace Realestate\SolrBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * References all Solr connections in a given Container.
 *
 */
class Registry
{
    private $container;
    private $connections;
    private $defaultConnection;

    public function __construct(ContainerInterface $container, array $connections, $defaultConnection)
    {
        $this->container = $container;
        $this->connections = $connections;
        $this->defaultConnection = $defaultConnection;
    }

    /**
     * Gets the default connection name.
     *
     * @return string The default connection name
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultConnection;
    }

    /**
     * Gets the named connection.
     *
     * @param string $name The connection name (null for the default one)
     *
     * @return Connection
     */
    public function getConnection($name = null)
    {
        if (null === $name) {
            $name = $this->defaultConnection;
        }

        if (!isset($this->connections[$name])) {
            throw new \InvalidArgumentException(sprintf('Doctrine Connection named "%s" does not exist.', $name));
        }

        return $this->container->get($this->connections[$name]);
    }

    /**
     * Gets an array of all registered connections
     *
     * @return array An array of Connection instances
     */
    public function getConnections()
    {
        $connections = array();
        foreach ($this->connections as $name => $id) {
            $connections[$name] = $this->container->get($id);
        }

        return $connections;
    }

    /**
     * Gets all connection names.
     *
     * @return array An array of connection names
     */
    public function getConnectionNames()
    {
        return $this->connections;
    }
    
    public function getDocumentManager()
    {
        return $this->container->get("solr.solarium.document_manager");
    }

  
 
}

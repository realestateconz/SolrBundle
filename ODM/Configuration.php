<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM;

use Doctrine\Common\Cache\Cache,
    Doctrine\Common\Cache\ArrayCache,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\Common\Annotations\AnnotationReader,
    Realestate\SolrBundle\ODM\Mapping\Driver\Driver,
    Realestate\SolrBundle\ODM\Driver\AnnotationDriver;

/**
 * 
 */
class Configuration extends \Doctrine\DBAL\Configuration
{

    /**
     * Sets the cache driver implementation that is used for metadata caching.
     *
     * @param Driver $driverImpl
     * @todo Force parameter to be a Closure to ensure lazy evaluation
     *       (as soon as a metadata cache is in effect, the driver never needs to initialize).
     */
    public function setMetadataDriverImpl(Driver $driverImpl)
    {
        $this->_attributes['metadataDriverImpl'] = $driverImpl;
    }

  
    /**
     * Gets the cache driver implementation that is used for the mapping metadata.
     *
     * @return Mapping\Driver\Driver
     */
    public function getMetadataDriverImpl()
    {
        return isset($this->_attributes['metadataDriverImpl']) ?
                $this->_attributes['metadataDriverImpl'] : null;
    }

  
    /**
     * Gets the cache driver implementation that is used for the query cache (SQL cache).
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getQueryCacheImpl()
    {
        return isset($this->_attributes['queryCacheImpl']) ?
                $this->_attributes['queryCacheImpl'] : null;
    }

    /**
     * Sets the cache driver implementation that is used for the query cache (SQL cache).
     *
     * @param \Doctrine\Common\Cache\Cache $cacheImpl
     */
    public function setQueryCacheImpl(Cache $cacheImpl)
    {
        $this->_attributes['queryCacheImpl'] = $cacheImpl;
    }

    /**
     * Gets the cache driver implementation that is used for metadata caching.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getMetadataCacheImpl()
    {
        return isset($this->_attributes['metadataCacheImpl']) ?
                $this->_attributes['metadataCacheImpl'] : null;
    }

    /**
     * Sets the cache driver implementation that is used for metadata caching.
     *
     * @param \Doctrine\Common\Cache\Cache $cacheImpl
     */
    public function setMetadataCacheImpl(Cache $cacheImpl)
    {
        $this->_attributes['metadataCacheImpl'] = $cacheImpl;
    }


    /**
     * Set a class metadata factory.
     * 
     * @param string $cmf
     */
    public function setClassMetadataFactoryName($cmfName)
    {
        $this->_attributes['classMetadataFactoryName'] = $cmfName;
    }

    /**
     * @return string
     */
    public function getClassMetadataFactoryName()
    {
        if (!isset($this->_attributes['classMetadataFactoryName'])) {
            $this->_attributes['classMetadataFactoryName'] = 'Realestate\SolrBundle\ODM\Mapping\ClassMetadataFactory';
        }
        return $this->_attributes['classMetadataFactoryName'];
    }
}
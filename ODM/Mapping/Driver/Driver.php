<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Mapping\Driver;

use Realestate\SolrBundle\ODM\Mapping\ClassMetadataInfo;

/**
 * Contract for metadata drivers.
 *
 */
interface Driver
{
    /**
     * Loads the metadata for the specified class into the provided container.
     * 
     * @param string $className
     * @param ClassMetadataInfo $metadata
     */
    function loadMetadataForClass($className, ClassMetadataInfo $metadata);
    
    /**
     * Gets the names of all mapped classes known to this driver.
     * 
     * @return array The names of all mapped classes known to this driver.
     */
    function getAllClassNames(); 

    /**
     * Whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped as an Entity or a
     * MappedSuperclass.
     *
     * @param string $className
     * @return boolean
     */
    function isTransient($className);
}
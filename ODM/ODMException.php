<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM;

use Exception;

/**
 * Base exception class for all ODM exceptions.
 *
 */
class ODMException extends Exception
{
    public static function missingMappingDriverImpl()
    {
        return new self("It's a requirement to specify a Metadata Driver and pass it ".
            "to Realestate\SolrBundle\ODM\Configuration::setMetadataDriverImpl().");
    }
    
    public static function entityMissingForeignAssignedId($entity, $relatedEntity)
    {
        return new self(
            "Entity of type " . get_class($entity) . " has identity through a foreign entity " . get_class($relatedEntity) . ", " .
            "however this entity has no ientity itself. You have to call EntityManager#persist() on the related entity " .
            "and make sure it an identifier was generated before trying to persist '" . get_class($entity) . "'. In case " .
            "of Post Insert ID Generation (such as MySQL Auto-Increment or PostgreSQL SERIAL) this means you have to call " .
            "EntityManager#flush() between both persist operations."
        );
    }

    public static function entityMissingAssignedId($entity)
    {
        return new self("Entity of type " . get_class($entity) . " is missing an assigned ID. " .
            "The identifier generation strategy for this entity requires the ID field to be populated before ".
            "EntityManager#persist() is called. If you want automatically generated identifiers instead " . 
            "you need to adjust the metadata mapping accordingly."
        );
    }

    public static function unrecognizedField($field)
    {
        return new self("Unrecognized field: $field");
    }

    public static function invalidFlushMode($mode)
    {
        return new self("'$mode' is an invalid flush mode.");
    }

    public static function entityManagerClosed()
    {
        return new self("The EntityManager is closed.");
    }

    public static function invalidHydrationMode($mode)
    {
        return new self("'$mode' is an invalid hydration mode.");
    }

    public static function mismatchedEventManager()
    {
        return new self("Cannot use different EventManager instances for EntityManager and Connection.");
    }

    public static function findByRequiresParameter($methodName)
    {
        return new self("You need to pass a parameter to '".$methodName."'");
    }

    public static function invalidFindByCall($entityName, $fieldName, $method)
    {
        return new self(
            "Entity '".$entityName."' has no field '".$fieldName."'. ".
            "You can therefore not call '".$method."' on the entities' repository"
        );
    }

    public static function invalidFindByInverseAssociation($entityName, $associationFieldName)
    {
        return new self(
            "You cannot search for the association field '".$entityName."#".$associationFieldName."', ".
            "because it is the inverse side of an association. Find methods only work on owning side associations."
        );
    }

    public static function invalidResultCacheDriver() {
        return new self("Invalid result cache driver; it must implement \Doctrine\Common\Cache\Cache.");
    }

    public static function notSupported() {
        return new self("This behaviour is (currently) not supported by Doctrine 2");
    }

    public static function queryCacheNotConfigured()
    {
        return new self('Query Cache is not configured.');
    }

    public static function metadataCacheNotConfigured()
    {
        return new self('Class Metadata Cache is not configured.');
    }

    public static function proxyClassesAlwaysRegenerating()
    {
        return new self('Proxy Classes are always regenerating.');
    }

    public static function unknownEntityNamespace($entityNamespaceAlias)
    {
        return new self(
            "Unknown Entity namespace alias '$entityNamespaceAlias'."
        );
    }
}

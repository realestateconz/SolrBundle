<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Adapter;

/**
 * Converts ORM entity to ODM entity
 *
 */
class ORM
{
    protected $inflectionCacheGetter = array();
    
    /**
     * @todo Use class metadata
     */
    public function convertFrom($ormObject, $odmObject)
    {
     
        $ormReflectionClass = new \ReflectionClass($ormObject);
        $odmReflectionClass = new \ReflectionClass($odmObject);
        
        $documents = array();
        
        foreach($odmReflectionClass->getProperties() as $property) {
            $ormGetter = $this->getPropertyGetter($property->getName());
            
            if($ormReflectionClass->hasMethod($ormGetter)) {
                $property->setAccessible(true);
                $value = $ormObject->$ormGetter();
                
                if($value instanceof \Doctrine\Common\Collections\Collection) {
                    $keys = array();
                    foreach($value as $enity) {
                        $keys[] = $enity->getId();
                    }
                    
                    $value = $keys;
                    
                    
                    
                }
                
                $property->setValue($odmObject, $value);
            }
        }
    }
    
    protected function getPropertyGetter($property)
    {
        if(!isset($this->inflectionCacheGetter[$property])) {
            $property = 'get ' . $property;
            
            $this->inflectionCacheGetter[$property] = \Doctrine\Common\Util\Inflector::camelize($property);
        }
        
        
        return $this->inflectionCacheGetter[$property];
    }
}
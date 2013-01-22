<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */


namespace Realestate\SolrBundle\ODM\Mapping;

use ReflectionException,
    Realestate\SolrBundle\ODM\ODMException,
    Realestate\SolrBundle\ODM\DocumentManager,
    Doctrine\Common\Persistence\Mapping\ClassMetadataFactory as ClassMetadataFactoryInterface;

/**
 * 
 */
class ClassMetadataFactory implements ClassMetadataFactoryInterface
{
    /**
     * @var DocumentManager
     */
    private $dm;
    

    /**
     * @var Driver\Driver
     */
    private $driver;

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cacheDriver;

    /**
     * @var array
     */
    private $loadedMetadata = array();

    /**
     * @var bool
     */
    private $initialized = false;
    
    /**
     * @param DocumentManager $dm
     */
    public function setDocumentManager(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Sets the cache driver used by the factory to cache ClassMetadata instances.
     *
     * @param Doctrine\Common\Cache\Cache $cacheDriver
     */
    public function setCacheDriver($cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
    }

    /**
     * Gets the cache driver used by the factory to cache ClassMetadata instances.
     *
     * @return Doctrine\Common\Cache\Cache
     */
    public function getCacheDriver()
    {
        return $this->cacheDriver;
    }
    
    public function getLoadedMetadata()
    {
        return $this->loadedMetadata;
    }
    
    /**
     * Forces the factory to load the metadata of all classes known to the underlying
     * mapping driver.
     * 
     * @return array The ClassMetadata instances of all mapped classes.
     */
    public function getAllMetadata()
    {
        if ( ! $this->initialized) {
            $this->initialize();
        }

        $metadata = array();
        foreach ($this->driver->getAllClassNames() as $className) {
            $metadata[] = $this->getMetadataFor($className);
        }

        return $metadata;
    }

    /**
     * Lazy initialization of this stuff, especially the metadata driver,
     * since these are not needed at all when a metadata cache is active.
     */
    private function initialize()
    {
        $this->driver = $this->dm->getConfiguration()->getMetadataDriverImpl();
        $this->initialized = true;
    }

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     * @return Realestate\SolrBundle\ODM\Mapping\ClassMetadata
     */
    public function getMetadataFor($className)
    {
        if ( ! isset($this->loadedMetadata[$className])) {
            $realClassName = $className;

            // Check for namespace alias
            if (strpos($className, ':') !== false) {
                list($namespaceAlias, $simpleClassName) = explode(':', $className);
                $realClassName = $this->dm->getConfiguration()->getDocumentNamespace($namespaceAlias) . '\\' . $simpleClassName;

                if (isset($this->loadedMetadata[$realClassName])) {
                    // We do not have the alias name in the map, include it
                    $this->loadedMetadata[$className] = $this->loadedMetadata[$realClassName];

                    return $this->loadedMetadata[$realClassName];
                }
            }

            if ($this->cacheDriver) {
                if (($cached = $this->cacheDriver->fetch("$realClassName\$CLASSMETADATA")) !== false) {
                    $this->loadedMetadata[$realClassName] = $cached;
                } else {
                    foreach ($this->loadMetadata($realClassName) as $loadedClassName) {
                        $this->cacheDriver->save(
                            "$loadedClassName\$CLASSMETADATA", $this->loadedMetadata[$loadedClassName], null
                        );
                    }
                }
            } else {
                $this->loadMetadata($realClassName);
            }

            if ($className != $realClassName) {
                // We do not have the alias name in the map, include it
                $this->loadedMetadata[$className] = $this->loadedMetadata[$realClassName];
            }
        }

        return $this->loadedMetadata[$className];
    }

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     * 
     * @param string $className
     * @return boolean TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor($className)
    {
        return isset($this->loadedMetadata[$className]);
    }

    /**
     * Sets the metadata descriptor for a specific class.
     * 
     * NOTE: This is only useful in very special cases, like when generating proxy classes.
     *
     * @param string $className
     * @param ClassMetadata $class
     */
    public function setMetadataFor($className, $class)
    {
        $this->loadedMetadata[$className] = $class;
    }

    /**
     * Get array of parent classes for the given entity class
     *
     * @param string $name
     * @return array $parentClasses
     */
    protected function getParentClasses($name)
    {
        // Collect parent classes, ignoring transient (not-mapped) classes.
        $parentClasses = array();
        foreach (array_reverse(class_parents($name)) as $parentClass) {
            if ( ! $this->driver->isTransient($parentClass)) {
                $parentClasses[] = $parentClass;
            }
        }
        return $parentClasses;
    }

    /**
     * Loads the metadata of the class in question and all it's ancestors whose metadata
     * is still not loaded.
     *
     * @param string $name The name of the class for which the metadata should get loaded.
     * @param array  $tables The metadata collection to which the loaded metadata is added.
     */
    protected function loadMetadata($name)
    {
        if ( ! $this->initialized) {
            $this->initialize();
        }

        $loaded = array();

        $parentClasses = $this->getParentClasses($name);
        $parentClasses[] = $name;

        // Move down the hierarchy of parent classes, starting from the topmost class
        $parent = null;
        $rootEntityFound = false;
        $visited = array();
        
        foreach ($parentClasses as $className) {
            if (isset($this->loadedMetadata[$className])) {
                $parent = $this->loadedMetadata[$className];
                
                $rootEntityFound = true;
                array_unshift($visited, $className);

                continue;
            }

            $class = $this->newClassMetadataInstance($className);

            if ($parent) {
                $class->setInheritanceType($parent->inheritanceType);
                $class->setIdGeneratorType($parent->generatorType);
                $this->addInheritedFields($class, $parent);
                $class->setIdentifier($parent->identifier);
            }

            // Invoke driver
            try {
                $this->driver->loadMetadataForClass($className, $class);
            } catch (ReflectionException $e) {
                throw MappingException::reflectionFailure($className, $e);
            }

            // If this class has a parent the id generator strategy is inherited.
            // However this is only true if the hierachy of parents contains the root entity,
            // if it consinsts of mapped superclasses these don't necessarily include the id field.
            if ($parent && $rootEntityFound) {
                if ($parent->isIdGeneratorSequence()) {
                    $class->setSequenceGeneratorDefinition($parent->sequenceGeneratorDefinition);
                } else if ($parent->isIdGeneratorTable()) {
                    $class->getTableGeneratorDefinition($parent->tableGeneratorDefinition);
                }
                if ($parent->generatorType) {
                    $class->setIdGeneratorType($parent->generatorType);
                }
                if ($parent->idGenerator) {
                    $class->setIdGenerator($parent->idGenerator);
                }
            } 

            if ($parent && $parent->isInheritanceTypeSingleTable()) {
                $class->setPrimaryTable($parent->table);
            }

            $class->setParentClasses($visited);

            // Verify & complete identifier mapping
            if ( ! $class->identifier) {
                throw MappingException::identifierRequired($className);
            }

           
            
            $this->loadedMetadata[$className] = $class;

            $parent = $class;

            
            $rootEntityFound = true;
            array_unshift($visited, $className);


            $loaded[] = $className;
        }

        return $loaded;
    }

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return Realestate\SolrBundle\ODM\Mapping\ClassMetadata
     */
    protected function newClassMetadataInstance($className)
    {
        return new ClassMetadata($className);
    }

    /**
     * Adds inherited fields to the subclass mapping.
     *
     * @param Realestate\SolrBundle\ODM\Mapping\ClassMetadata $subClass
     * @param Realestate\SolrBundle\ODM\Mapping\ClassMetadata $parentClass
     */
    private function addInheritedFields(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->fieldMappings as $fieldName => $mapping) {
            if ( ! isset($mapping['inherited']) && ! $parentClass->isMappedSuperclass) {
                $mapping['inherited'] = $parentClass->name;
            }
            if ( ! isset($mapping['declared'])) {
                $mapping['declared'] = $parentClass->name;
            }
            $subClass->addInheritedFieldMapping($mapping);
        }
        foreach ($parentClass->reflFields as $name => $field) {
            $subClass->reflFields[$name] = $field;
        }
    }

    
   
}

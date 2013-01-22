<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Mapping;

use ReflectionClass, ReflectionProperty;

class ClassMetadata extends ClassMetadataInfo
{
    /**
     * The ReflectionProperty instances of the mapped class.
     *
     * @var array
     */
    public $reflFields = array();

    /**
     * The prototype from which new instances of the mapped class are created.
     *
     * @var object
     */
    private $prototype;

    /**
     * Initializes a new ClassMetadata instance that will hold the object-document mapping
     * metadata of the class with the given name.
     *
     * @param string $documentName The name of the document class the new instance is used for.
     */
    public function __construct($documentName)
    {
        parent::__construct($documentName);
        $this->reflClass = new ReflectionClass($documentName);
        
        $this->namespace = $this->reflClass->getNamespaceName();
        
        $customRepositoryClassName = '\\' . str_replace('\\Document\\', '\\Repository\\Solr\\', $documentName) . 'Repository';
        
        if(class_exists($customRepositoryClassName)) {
            $this->customRepositoryClassName = $customRepositoryClassName;
        }
        
        
        
    }
    
    
    /**
     * Determines which fields get serialized.
     *
     * It is only serialized what is necessary for best unserialization performance.
     * That means any metadata properties that are not set or empty or simply have
     * their default value are NOT serialized.
     * 
     * Parts that are also NOT serialized because they can not be properly unserialized:
     *      - reflClass (ReflectionClass)
     *      - reflFields (ReflectionProperty array)
     * 
     * @return array The names of all the fields that should be serialized.
     */
    public function __sleep()
    {
        // This metadata is always serialized/cached.
        $serialized = array(
            'fieldMappings',
            'fieldNames',
            'identifier',
            'name',
            'solrCore', 
            'ormEntity',
            'fieldGetters',
            'fieldSetters'
        );


        if ($this->customRepositoryClassName) {
            $serialized[] = 'customRepositoryClassName';
        }

      
        return $serialized;
    }
    
    /**
     * Restores some state that can not be serialized/unserialized.
     * 
     * @return void
     */
    public function __wakeup()
    {
        // Restore ReflectionClass and properties
        $this->reflClass = new ReflectionClass($this->name);

        foreach ($this->fieldMappings as $field => $mapping) {
            if (isset($mapping['declared'])) {
                $reflField = new ReflectionProperty($mapping['declared'], $field);
            } else {
                $reflField = $this->reflClass->getProperty($field);
            }
            $reflField->setAccessible(true);
            $this->reflFields[$field] = $reflField;
        }
    }

 
    /**
     * Creates a new instance of the mapped class, without invoking the constructor.
     *
     * @return object
     */
    public function newInstance()
    {
        if ($this->prototype === null) {
            $this->prototype = unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->name), $this->name));
        }
        return clone $this->prototype;
    }
    
    /**
     * Gets the ReflectionPropertys of the mapped class.
     *
     * @return array An array of ReflectionProperty instances.
     */
    public function getReflectionProperties()
    {
        return $this->reflFields;
    }

    /**
     * Gets a ReflectionProperty for a specific field of the mapped class.
     *
     * @param string $name
     * @return ReflectionProperty
     */
    public function getReflectionProperty($name)
    {
        return $this->reflFields[$name];
    }
    
    
    /**
     * Sets the specified field to the specified value on the given entity.
     *
     * @param object $entity
     * @param string $field
     * @param mixed $value
     */
    public function setFieldValue($document, $field, $value)
    {
        $this->reflFields[$field]->setValue($document, $value);
    }

    /**
     * Gets the specified field's value off the given entity.
     *
     * @param object $entity
     * @param string $field
     */
    public function getFieldValue($document, $field)
    {
        return $this->reflFields[$field]->getValue($document);
    }
    
    
    /**
     * Gets the specified field's value off the given entity.
     *
     * @param object $entity
     * @param string $field
     */
    public function getFieldProcessedValue($document, $field)
    {
        if(null !== ($getter = $this->getFieldGetter($field))) {
            return $document->$getter();
        }
        
        return $this->getFieldValue($document, $field);
    }
    
    public function getFieldGetter($field)
    {
        if(isset($this->fieldGetters[$field])) {
            return $this->fieldGetters[$field];
        }
        
        return null;
    }
    
    /**
     *
     * @param string $field
     * @return bool
     */
    public function getFieldIsMulti($field)
    {
        if(isset($this->fieldMappings[$field]['multiValued']) && $this->fieldMappings[$field]['multiValued'] === true) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Validates & completes the given field mapping.
     *
     * @param array $mapping  The field mapping to validated & complete.
     * @return array  The validated and completed field mapping.
     * 
     * @throws MappingException
     */
    protected function _validateAndCompleteFieldMapping(array &$mapping)
    {
        parent::_validateAndCompleteFieldMapping($mapping);

        // Store ReflectionProperty of mapped field
        $refProp = $this->reflClass->getProperty($mapping['fieldName']);
        $refProp->setAccessible(true);
        $this->reflFields[$mapping['fieldName']] = $refProp;
    }
}

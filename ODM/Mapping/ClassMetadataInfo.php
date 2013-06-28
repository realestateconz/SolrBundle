<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */


namespace Realestate\SolrBundle\ODM\Mapping;

use ReflectionClass;


class ClassMetadataInfo implements \Doctrine\Common\Persistence\Mapping\ClassMetadata
{

    /**
     * READ-ONLY: The field name of the document identifier.
     */
    public $identifier;


    /**
     * READ-ONLY: The name of the document class.
     */
    public $name;

    /**
     * READ-ONLY: The namespace the document class is contained in.
     *
     * @var string
     * @todo Not really needed. Usage could be localized.
     */
    public $namespace;

  

    /**
     * The name of the custom repository class used for the document class.
     * (Optional).
     *
     * @var string
     */
    public $customRepositoryClassName;

   

    /**
     * READ-ONLY: The field mappings of the class.
     * Keys are field names and values are mapping definitions.
     *
     * The mapping definition array has the following values:
     *
     * - <b>fieldName</b> (string)
     * The name of the field in the Document.
     *
     * - <b>id</b> (boolean, optional)
     * Marks the field as the primary key of the document. Multiple fields of an
     * document can have the id attribute, forming a composite key.
     *
     * @var array
     */
    public $fieldMappings = array();
    
    /**
     * READ-ONLY: An array of field names. Used to look up field names from column names.
     * Keys are column names and values are field names.
     * This is the reverse lookup map of $_columnNames.
     *
     * @var array
     */
    public $fieldNames = array();
   
    public $associationMappings = array();
    
    public $fieldGetters = array();
    public $fieldSetters = array();

    /**
     * The ReflectionClass instance of the mapped class.
     *
     * @var ReflectionClass
     */
    public $reflClass;
    
    public $solrCore = null;
    
    public $ormEntity = null;
    

    /**
     * Initializes a new ClassMetadata instance that will hold the object-document mapping
     * metadata of the class with the given name.
     *
     * @param string $documentName The name of the document class the new instance is used for.
     */
    public function __construct($documentName)
    {
        $this->name = $documentName;
        $this->rootDocumentName = $documentName;
    }

    /**
     * Gets the ReflectionClass instance of the mapped class.
     *
     * @return ReflectionClass
     */
    public function getReflectionClass()
    {
        if ( ! $this->reflClass) {
            $this->reflClass = new ReflectionClass($this->name);
        }
        return $this->reflClass;
    }
    
    public function getName()
    {
        return $this->name;
    }

    /**
     * Checks whether a field is part of the identifier/primary key field(s).
     *
     * @param string $fieldName  The field name
     * @return boolean  TRUE if the field is part of the table identifier/primary key field(s),
     *                  FALSE otherwise.
     */
    public function isIdentifier($fieldName)
    {
        return $this->identifier === $fieldName ? true : false;
    }

    /**
     * INTERNAL:
     * Sets the mapped identifier field of this class.
     *
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }
    
    public function setSolrCore($solrCore)
    {
        $this->solrCore = $solrCore;
    }
    
    public function getSolrCore()
    {
        return $this->solrCore;
    }
    
    public function setOrmEntity($ormEntity)
    {
        $this->ormEntity = $ormEntity;
    }
    
    public function getOrmEntity()
    {
        return $this->ormEntity;
    }

    /**
     * Gets the mapped identifier field of this class.
     *
     * @return string $identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Checks whether the class has a (mapped) field with a certain name.
     *
     * @return boolean
     */
    public function hasField($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]);
    }

    /**
     * Sets the inheritance type used by the class and it's subclasses.
     *
     * @param integer $type
     */
    public function setInheritanceType($type)
    {
        $this->inheritanceType = $type;
    }

    /**
     * Checks whether a mapped field is inherited from an entity superclass.
     *
     * @return boolean TRUE if the field is inherited, FALSE otherwise.
     */
    public function isInheritedField($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]['inherited']);
    }

    /**
     * Registers a custom repository class for the document class.
     *
     * @param string $mapperClassName  The class name of the custom mapper.
     */
    public function setCustomRepositoryClass($repositoryClassName)
    {
        $this->customRepositoryClassName = $repositoryClassName;
    }
    
    
    /**
     * Adds a mapped field to the class.
     *
     * @param array $mapping The field mapping.
     */
    public function mapField(array $mapping)
    {
        $this->_validateAndCompleteFieldMapping($mapping);
        
        if (isset($this->fieldMappings[$mapping['fieldName']]) || isset($this->associationMappings[$mapping['fieldName']])) {
            throw MappingException::duplicateFieldMapping($this->name, $mapping['fieldName']);
        }
        
        $this->fieldMappings[$mapping['fieldName']] = $mapping;
    }
    
    
    /**
     * Validates & completes the given field mapping.
     *
     * @param array $mapping  The field mapping to validated & complete.
     * @return array  The validated and completed field mapping.
     */
    protected function _validateAndCompleteFieldMapping(array &$mapping)
    {
        // Check mandatory fields
        if ( ! isset($mapping['fieldName']) || strlen($mapping['fieldName']) == 0) {
            throw MappingException::missingFieldName($this->name);
        }
        if ( ! isset($mapping['type'])) {
            // Default to string
            $mapping['type'] = 'string';
        }
        
        

        // Complete id mapping
        if (isset($mapping['id']) && $mapping['id'] === true) {
            if ( $mapping['fieldName'] !== $this->identifier) {
                $this->identifier = $mapping['fieldName'];
            }
        }
    }
    
    
    /**
     * Sets the parent class names.
     * Assumes that the class names in the passed array are in the order:
     * directParent -> directParentParent -> directParentParentParent ... -> root.
     */
    public function setParentClasses(array $classNames)
    {
        $this->parentClasses = $classNames;
        if (count($classNames) > 0) {
            $this->rootEntityName = array_pop($classNames);
        }
    }
    
    
    /**
     * Checks whether the class has a mapped association for the specified field
     * and if yes, checks whether it is a single-valued association (to-one).
     *
     * @param string $fieldName
     * @return boolean TRUE if the association exists and is single-valued, FALSE otherwise.
     */
    public function isSingleValuedAssociation($fieldName)
    {
        return isset($this->associationMappings[$fieldName]) &&
                ($this->associationMappings[$fieldName]['type'] & self::TO_ONE);
    }
    
    
    /**
     * Gets the type of a field.
     *
     * @param string $fieldName
     * @return Doctrine\DBAL\Types\Type
     */
    public function getTypeOfField($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]) ?
                $this->fieldMappings[$fieldName]['type'] : null;
    }
    
    /**
     * A numerically indexed list of field names of this persistent class.
     * 
     * This array includes identifier fields if present on this class.
     * 
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->fieldMappings);
    }
    
    /**
     * A numerically indexed list of association names of this persistent class.
     * 
     * This array includes identifier associations if present on this class.
     * 
     * @return array
     */
    public function getAssociationNames()
    {
        return array_keys($this->associationMappings);
    }

    /**
     * Checks if the given field is a mapped collection valued association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isCollectionValuedAssociation($fieldName)
    {
        // @todo
        
        return false;
    }
    
    /**
     * Checks if the given field is a mapped association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function hasAssociation($fieldName)
    {
        // @todo
        return false;
    }
    
    
    /**
     * Returns the target class name of the given association.
     * 
     * @param string $assocName
     * @return string
     */
    public function getAssociationTargetClass($assocName)
    {
        // @todo
        return null;
    }
    
    /**
     * Returns an array of identifier field names numerically indexed.
     *
     * @return array
     */
    public function getIdentifierFieldNames()
    {
        return array($this->getIdentifier());
    }
    
    /**
     * Checks if the association is the inverse side of a bidirectional association
     *
     * @param string $assocName
     * @return boolean
     */
    public function isAssociationInverseSide($assocName)
    {
        return false;
    }
    
    /**
     * Returns the target field of the owning side of the association
     *
     * @param string $assocName
     * @return string
     */
    public function getAssociationMappedByTargetField($assocName)
    {
        return null;
    }
    
    /**
     * Return the identifier of this object as an array with field name as key.
     *
     * Has to return an empty array if no identifier isset.
     *
     * @param object $object
     * @return array
     */
    public function getIdentifierValues($object)
    {
        // @todo
        return array();
    }
}
<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Mapping\Driver;

use Doctrine\Common\Cache\ArrayCache,
    Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Realestate\SolrBundle\ODM\Mapping\ClassMetadataInfo,
    Realestate\SolrBundle\ODM\Mapping\MappingException;

/**
 * 
 */
class AnnotationDriver implements Driver
{
    /**
     * The AnnotationReader.
     *
     * @var AnnotationReader
     */
    protected $_reader;

    /**
     * The paths where to look for mapping files.
     *
     * @var array
     */
    protected $_paths = array();

    /**
     * The file extension of mapping documents.
     *
     * @var string
     */
    protected $_fileExtension = '.php';

    /**
     * @param array
     */
    protected $_classNames;

    /**
     * Initializes a new AnnotationDriver that uses the given AnnotationReader for reading
     * docblock annotations.
     *
     * @param AnnotationReader $reader The AnnotationReader to use, duck-typed.
     * @param string|array $paths One or multiple paths where mapping classes can be found.
     */
    public function __construct($reader, $paths = null)
    {
        $this->_reader = $reader;
        if ($paths) {
            $this->addPaths((array) $paths);
        }
    }

    /**
     * Append lookup paths to metadata driver.
     *
     * @param array $paths
     */
    public function addPaths(array $paths)
    {
        $this->_paths = array_unique(array_merge($this->_paths, $paths));
    }

    /**
     * Retrieve the defined metadata lookup paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->_paths;
    }

    /**
     * Get the file extension used to look for mapping files under
     *
     * @return void
     */
    public function getFileExtension()
    {
        return $this->_fileExtension;
    }

    /**
     * Set the file extension used to look for mapping files under
     *
     * @param string $fileExtension The file extension to set
     * @return void
     */
    public function setFileExtension($fileExtension)
    {
        $this->_fileExtension = $fileExtension;
    }

    
    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadataInfo $metadata)
    {
        $class = $metadata->getReflectionClass();

        $classAnnotations = $this->_reader->getClassAnnotations($class);
        
        // Compatibility with Doctrine Common 3.x
        if ($classAnnotations && is_int(key($classAnnotations))) {
            foreach ($classAnnotations as $annot) {
                $classAnnotations[get_class($annot)] = $annot;
            }
        }

        // Evaluate Document annotation
        if (isset($classAnnotations['Realestate\SolrBundle\ODM\Annotation\Document'])) {
            $documentAnnot = $classAnnotations['Realestate\SolrBundle\ODM\Annotation\Document'];
            $metadata->setCustomRepositoryClass($documentAnnot->repositoryClass);

            if ($documentAnnot->readOnly) {
                $metadata->markReadOnly();
            }
            
            if ($documentAnnot->solrCore) {
                $metadata->setSolrCore($documentAnnot->solrCore);
            }
            
            if ($documentAnnot->ormEntity) {
                $metadata->setOrmEntity($documentAnnot->ormEntity);
            }
        } else {
            throw MappingException::classIsNotAValidEntityOrMappedSuperClass($className);
        }
        
        


        // Evaluate annotations on properties/fields
        foreach ($class->getProperties() as $property) {
            $mapping = array();
            $mapping['fieldName'] = $property->getName();

            // Fields
            if ($fieldAnnot = $this->_reader->getPropertyAnnotation($property, 'Realestate\SolrBundle\ODM\Annotation\Column')) {
                if ($fieldAnnot->type == null) {
                    throw MappingException::propertyTypeIsRequired($className, $property->getName());
                }

                $mapping['type'] = $fieldAnnot->type;
                $mapping['length'] = $fieldAnnot->length;
                $mapping['precision'] = $fieldAnnot->precision;
                $mapping['nullable'] = $fieldAnnot->nullable;
                
                $mapping['indexed'] = ($fieldAnnot->indexed === 'false') ? false : true;
                $mapping['stored'] = ($fieldAnnot->stored === 'false') ? false : true;
                $mapping['multiValued'] = ($fieldAnnot->multiValued === 'true') ? true : false;
                $mapping['required'] = $fieldAnnot->required;
                
                if(null !== $fieldAnnot->omitNorms) {
                    $mapping['omitNorms'] = ($fieldAnnot->omitNorms === 'true') ? true : false;
                }
                
                
                $mapping['boost'] = $fieldAnnot->boost;

                if ($fieldAnnot->options) {
                    $mapping['options'] = $fieldAnnot->options;
                }

                if (isset($fieldAnnot->name)) {
                    $mapping['fieldName'] = $fieldAnnot->name;
                }
                
                if (isset($fieldAnnot->sqlColumnName)) {
                    $mapping['sqlColumnName'] = $fieldAnnot->sqlColumnName;
                } else {
                    $mapping['sqlColumnName'] = $property->getName();
                }
                
                if (isset($fieldAnnot->sqlExpr)) {
                    $mapping['sqlExpr'] = $fieldAnnot->sqlExpr;
                }
                
                if (isset($fieldAnnot->sqlJoin)) {
                    $mapping['sqlJoin'] = $fieldAnnot->sqlJoin;
                }
                
                if (isset($fieldAnnot->dqlJoin)) {
                    $mapping['dqlJoin'] = $fieldAnnot->dqlJoin;
                    
                    if (isset($fieldAnnot->dqlJoinType)) {
                        $mapping['dqlJoinType'] = $fieldAnnot->dqlJoinType;
                    }
                    
                    if (isset($fieldAnnot->dqlJoinAlias)) {
                        $mapping['dqlJoinAlias'] = $fieldAnnot->dqlJoinAlias;
                    }

                    if (isset($fieldAnnot->dqlJoinCondition)) {
                        $mapping['dqlJoinCondition'] = $fieldAnnot->dqlJoinCondition;
                    }
                }
                
                
                
                
                
                
                
                

                if (isset($fieldAnnot->columnDefinition)) {
                    $mapping['columnDefinition'] = $fieldAnnot->columnDefinition;
                }

                if ($idAnnot = $this->_reader->getPropertyAnnotation($property, 'Realestate\SolrBundle\ODM\Annotation\Id')) {
                    $mapping['id'] = true;
                }
                
                // getter
                $getterMethodName = \Doctrine\Common\Util\Inflector::camelize('get_' . $property->getName());
                if ($class->hasMethod($getterMethodName)) {
                    $method = $class->getMethod($getterMethodName);
                    if($method->isPublic() && !$method->isStatic()) {
                        $metadata->fieldGetters[$mapping['fieldName']] = $getterMethodName;
                        $mapping['getter'] = $getterMethodName;
                    }
                    
                }
                
                // setter
                $setterMethodName = \Doctrine\Common\Util\Inflector::camelize('set_' . $property->getName());
                if ($class->hasMethod($setterMethodName)) {
                    $method = $class->getMethod($setterMethodName);
                    if($method->isPublic() && !$method->isStatic()) {
                        $metadata->fieldSetters[$mapping['fieldName']] = $setterMethodName;
                        $mapping['setter'] = $getterMethodName;
                    }
                    
                }


                $metadata->mapField($mapping);

            }
        }

        // Evaluate @HasLifecycleCallbacks annotation
        
    }

    /**
     * Whether the class with the specified name is transient. Only non-transient
     * classes, that is entities and mapped superclasses, should have their metadata loaded.
     * A class is non-transient if it is annotated with either @Entity or
     * @MappedSuperclass in the class doc block.
     *
     * @param string $className
     * @return boolean
     */
    public function isTransient($className)
    {
        $classAnnotations = $this->_reader->getClassAnnotations(new \ReflectionClass($className));

        // Compatibility with Doctrine Common 3.x
        if ($classAnnotations && is_int(key($classAnnotations))) {
            foreach ($classAnnotations as $annot) {
                if ($annot instanceof \Doctrine\ORM\Mapping\Entity) {
                    return false;
                }
                if ($annot instanceof \Doctrine\ORM\Mapping\MappedSuperclass) {
                    return false;
                }
            }

            return true;
        }

        return ! isset($classAnnotations['Doctrine\ORM\Mapping\Entity']);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames()
    {
        if ($this->_classNames !== null) {
            return $this->_classNames;
        }

        if (!$this->_paths) {
            throw MappingException::pathRequired();
        }

        $classes = array();
        $includedFiles = array();

        foreach ($this->_paths as $path) {
            if ( ! is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+\\' . $this->_fileExtension . '$/i', 
                \RecursiveRegexIterator::GET_MATCH
            );
            
            foreach ($iterator as $file) {
                $sourceFile = realpath($file[0]);
                
                require_once $sourceFile;
                
                $includedFiles[] = $sourceFile;
            }
        }

        $declared = get_declared_classes();

        foreach ($declared as $className) {
            $rc = new \ReflectionClass($className);
            $sourceFile = $rc->getFileName();
            if (in_array($sourceFile, $includedFiles) && ! $this->isTransient($className)) {
                $classes[] = $className;
            }
        }

        $this->_classNames = $classes;

        return $classes;
    }

    /**
     * Factory method for the Annotation Driver
     *
     * @param array|string $paths
     * @param AnnotationReader $reader
     * @return AnnotationDriver
     */
    static public function create($paths = array(), AnnotationReader $reader = null)
    {
        if ($reader == null) {
            $reader = new AnnotationReader();
            $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
        }
        return new self($reader, $paths);
    }
}

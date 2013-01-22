<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Mapping;

/**
 * A MappingException indicates that something is wrong with the mapping setup.
 *
 * @since 2.0
 */
class MappingException extends \Realestate\SolrBundle\ODM\ODMException
{
    public static function pathRequired()
    {
        return new self("Specifying the paths to your entities is required ".
            "in the AnnotationDriver to retrieve all class names.");
    }

    public static function identifierRequired($entityName)
    {
        return new self("No identifier/primary key specified for Entity '$entityName'."
                . " Every Entity must have an identifier/primary key.");
    }

    public static function invalidInheritanceType($entityName, $type)
    {
        return new self("The inheritance type '$type' specified for '$entityName' does not exist.");
    }

    public static function generatorNotAllowedWithCompositeId()
    {
        return new self("Id generators can't be used with a composite id.");
    }

    public static function missingFieldName($entity)
    {
        return new self("The field or association mapping misses the 'fieldName' attribute in entity '$entity'.");
    }

    public static function mappingFileNotFound($entityName, $fileName)
    {
        return new self("No mapping file found named '$fileName' for class '$entityName'.");
    }

    public static function mappingNotFound($className, $fieldName)
    {
        return new self("No mapping found for field '$fieldName' on class '$className'.");
    }

    public static function queryNotFound($className, $queryName)
    {
        return new self("No query found named '$queryName' on class '$className'.");
    }

    /**
     * Called if a required option was not found but is required
     *
     * @param string $field which field cannot be processed?
     * @param string $expectedOption which option is required
     * @param string $hint  Can optionally be used to supply a tip for common mistakes,
     *                      e.g. "Did you think of the plural s?"
     * @return MappingException
     */
    static function missingRequiredOption($field, $expectedOption, $hint = '')
    {
        $message = "The mapping of field '{$field}' is invalid: The option '{$expectedOption}' is required.";

        if ( ! empty($hint)) {
            $message .= ' (Hint: ' . $hint . ')';
        }

        return new self($message);
    }

    /**
     * Generic exception for invalid mappings.
     *
     * @param string $fieldName
     */
    public static function invalidMapping($fieldName)
    {
        return new self("The mapping of field '$fieldName' is invalid.");
    }

    /**
     * Exception for reflection exceptions - adds the entity name,
     * because there might be long classnames that will be shortened
     * within the stacktrace
     *
     * @param string $entity The entity's name
     * @param \ReflectionException $previousException
     */
    public static function reflectionFailure($entity, \ReflectionException $previousException)
    {
        return new self('An error occurred in ' . $entity, 0, $previousException);
    }


    public static function classIsNotAValidEntityOrMappedSuperClass($className)
    {
        return new self('Class '.$className.' is not a valid entity or mapped super class.');
    }

    public static function propertyTypeIsRequired($className, $propertyName)
    {
        return new self("The attribute 'type' is required for the column description of property ".$className."::\$".$propertyName.".");
    }

    public static function tableIdGeneratorNotImplemented($className)
    {
        return new self("TableIdGenerator is not yet implemented for use with class ".$className);
    }

    /**
     *
     * @param string $entity The entity's name
     * @param string $fieldName The name of the field that was already declared
     */
    public static function duplicateFieldMapping($entity, $fieldName) {
        return new self('Property "'.$fieldName.'" in "'.$entity.'" was already declared, but it must be declared only once');
    }

    public static function duplicateAssociationMapping($entity, $fieldName) {
        return new self('Property "'.$fieldName.'" in "'.$entity.'" was already declared, but it must be declared only once');
    }

    public static function duplicateQueryMapping($entity, $queryName) {
        return new self('Query named "'.$queryName.'" in "'.$entity.'" was already declared, but it must be declared only once');
    }

    public static function singleIdNotAllowedOnCompositePrimaryKey($entity) {
        return new self('Single id is not allowed on composite primary key in entity '.$entity);
    }

    public static function unsupportedOptimisticLockingType($entity, $fieldName, $unsupportedType) {
        return new self('Locking type "'.$unsupportedType.'" (specified in "'.$entity.'", field "'.$fieldName.'") '
                        .'is not supported by Doctrine.'
        );
    }

    public static function fileMappingDriversRequireConfiguredDirectoryPath($path = null)
    {
        if ( ! empty($path)) {
            $path = '[' . $path . ']';
        }
        
        return new self(
            'File mapping drivers must have a valid directory path, ' .
            'however the given path ' . $path . ' seems to be incorrect!'
        );
    }

    /**
     * Throws an exception that indicates that a class used in a discriminator map does not exist.
     * An example would be an outdated (maybe renamed) classname.
     *
     * @param string $className The class that could not be found
     * @param string $owningClass The class that declares the discriminator map.
     * @return self
     */
    public static function invalidClassInDiscriminatorMap($className, $owningClass) {
        return new self(
            "Entity class '$className' used in the discriminator map of class '$owningClass' ".
            "does not exist."
        );
    }

    public static function missingDiscriminatorMap($className)
    {
        return new self("Entity class '$className' is using inheritance but no discriminator map was defined.");
    }

    public static function missingDiscriminatorColumn($className)
    {
        return new self("Entity class '$className' is using inheritance but no discriminator column was defined.");
    }

    public static function invalidDiscriminatorColumnType($className, $type)
    {
        return new self("Discriminator column type on entity class '$className' is not allowed to be '$type'. 'string' or 'integer' type variables are suggested!");
    }

    public static function cannotVersionIdField($className, $fieldName)
    {
        return new self("Setting Id field '$fieldName' as versionale in entity class '$className' is not supported.");
    }

    /**
     * @param  string $className
     * @param  string $columnName
     * @return self
     */
    public static function duplicateColumnName($className, $columnName)
    {
        return new self("Duplicate definition of column '".$columnName."' on entity '".$className."' in a field or discriminator column mapping.");
    }

    public static function illegalToManyAssocationOnMappedSuperclass($className, $field)
    {
        return new self("It is illegal to put an inverse side one-to-many or many-to-many association on mapped superclass '".$className."#".$field."'.");
    }


    public static function noFieldNameFoundForColumn($className, $column)
    {
        return new self("Cannot find a field on '$className' that is mapped to column '$column'. Either the ".
            "field does not exist or an association exists but it has multiple join columns.");
    }

}
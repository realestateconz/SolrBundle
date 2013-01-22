<?php


namespace Realestate\SolrBundle\ODM\Mapping;

use Doctrine\Common\Annotations\Annotation;

/* Annotations */

/** @Annotation */
final class Document extends Annotation {
    public $repositoryClass;
    public $readOnly = false;
    public $core = null;
}

/** @Annotation */
final class Id extends Annotation {}


/** @Annotation */
final class Column extends Annotation {
    public $type = 'string';
    public $length;
    // The precision for a decimal (exact numeric) column (Applies only for decimal column)
    public $precision = 0;
    // The scale for a decimal (exact numeric) column (Applies only for decimal column)
    public $scale = 0;
    public $unique = false;
    public $nullable = false;
    public $name;
    public $options = array();
    public $columnDefinition;
}


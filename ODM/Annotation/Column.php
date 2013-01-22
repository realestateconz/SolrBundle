<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Annotation;

use Realestate\SolrBundle\Exception\RuntimeException;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Column extends Annotation
{
    public $type;
    
    public $length;
    
    public $precision;
    
    public $nullable;
    
    public $options = array();
    
    public $fieldName;
    
    public $indexed = true;
    
    public $stored = true;
    
    public $multiValued = false;
    
    public $required = false;
    
    public $copyField = false;
    
    public $omitNorms;
    
    public $sqlColumnName;
    
    public $sqlExpr;
    
    public $sqlJoin;
    
    
    public $dqlJoin;
    
    public $dqlJoinType = 'left';
    
    public $dqlJoinAlias;
    
    public $dqlJoinCondition;
    
    public $boost;
}
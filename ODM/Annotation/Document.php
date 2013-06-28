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
 * @Target("CLASS")
 */
final class Document extends Annotation
{
    public $solrCore;
    
    public $ormEntity;
    
    public $repositoryClass;
    
    public $readOnly = false;
    
    public $defaultSearchField;

}
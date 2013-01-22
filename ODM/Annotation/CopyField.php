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
final class CopyField extends Annotation
{
    public $sourceFields = array();
    
}
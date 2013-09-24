<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium\Query;

class Select extends \Solarium\Query\Select
{
    
    /**
     * Default options
     * 
     * @var array
     */
    protected $_options = array(
        'handler'       => 'select',
        'resultclass'   => '\Realestate\SolrBundle\Bridge\Solarium\Result\Select',
        'documentclass' => '\Solarium\Document\ReadOnly',       
        'query'         => '*:*',
        'start'         => 0,
        'rows'          => 100,
        'fields'        => '*,score',
        'dm'            => null
    );
    
    /**
     *
     * @return Realestate\SolrBundle\ODM\DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->getOption('dm');
    }
    
    /**
     *
     * @param Realestate\SolrBundle\ODM\DocumentManager $dm
     * @return type 
     */
    public function setDocumentManager($dm)
    {
        return $this->_setOption('dm', $dm);
    }
    
    
    /**
     * Defines the processing mode to be used during hydration / result set transformation.
     *
     */
    public function setHydrationMode($hydrationMode)
    {
        return $this->_setOption('hydrationMode', $hydrationMode);
    }

    /**
     * Gets the hydration mode currently used by the query.
     *
     * @return string
     */
    public function getHydrationMode()
    {
        return (null === $this->getOption('hydrationMode')) ? 'Object' : $this->getOption('hydrationMode');
    }
    

    public function setGeoDistance($distance)
    {
        return $this->_setOption('geo_distance', $distance);
    }

    public function getGeoDistance()
    {
        return $this->getOption('geo_distance');
    }

    public function setGeoPoint($latitude, $longitude)
    {
        return $this->_setOption('geo_point', $latitude . ',' . $longitude);
    }

    public function getGeoPoint()
    {
        return $this->getOption('geo_point');
    }

    public function setGeoColumn($column)
    {
        return $this->_setOption('geo_column', $column);
    }

    public function getGeoColumn()
    {
        return $this->getOption('geo_column');
    }

}

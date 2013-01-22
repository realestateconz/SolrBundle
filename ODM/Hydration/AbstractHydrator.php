<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Hydration;
use Realestate\SolrBundle\ODM\DocumentManager;


/**
 * Base class for all hydrators. 
 */
abstract class AbstractHydrator
{
    
    /** 
     * @var DocumentManager
     */
    protected $_dm;

    /** @var array The cache used during row-by-row hydration. */
    protected $_cache = array();

    /** @var Statement The statement that provides the data to hydrate. */
    protected $_result;
    
    protected $_metadata;

    
    /**
     *
     * @param Realestate\SolrBundle\DocumentManager $dm The DocumentManager to use.
     */
    public function __construct(DocumentManager $dm)
    {
        $this->_dm = $dm;
    }

    /**
     * Hydrates all rows returned by the passed statement instance at once.
     *
     * @param object $result
     * @return mixed
     */
    public function hydrateAll($result, $metadata)
    {
        $this->_result = $result;
        $this->_metadata = $metadata;
        $this->_prepare();
        $result = $this->_hydrateAll();
        $this->_cleanup();
        
        return $result;
    }

   
    /**
     * Excutes one-time preparation tasks, once each time hydration is started
     * through {@link hydrateAll} or {@link iterate()}.
     */
    protected function _prepare()
    {}

    /**
     * Excutes one-time cleanup tasks at the end of a hydration that was initiated
     * through {@link hydrateAll} or {@link iterate()}.
     */
    protected function _cleanup()
    {
        $this->_result = null;
        $this->_metadata = null;
    }

    /**
     * Hydrates all rows from the current statement instance at once.
     */
    abstract protected function _hydrateAll();

   
}

<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium\Query;

class Update extends \Solarium_Query_Update
{
    
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

    
}

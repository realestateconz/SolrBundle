<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium\Result;

class Select extends \Solarium\Result\Select
{

    
    public function getHasMoreResults()
    {
        $this->getQuery();
        
        $requestedRows = $this->getQuery()->getOption('rows');
        
        $startIndex = $this->getQuery()->getOption('start');
        
        if($requestedRows + $startIndex < $this->getNumFound()) {
            return true;
        }

        return false;
    }
    
    public function getResponseDocsRaw()
    {
        $data = $this->getData();
        
        return $data['response']['docs'];
    }
    
    public function getIds()
    {
        $data = $this->getData();
        
        $ids = array();

        if(isset($data['response']['docs'])) {
            foreach($data['response']['docs'] as $row) {
                if(isset($row['id'])) {
                    $ids[] = $row['id'];
                }
                
            }
        }
        
        return $ids;
    }

	// TODO
	public function getFacets() {
		return $this->getFacetSet();
	}
}
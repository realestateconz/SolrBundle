<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Hydration;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * The ArrayHydrator constructs an array out of a solr result set.
  */
class ArrayHydrator extends AbstractHydrator
{


    /**
     * {@inheritdoc}
     */
    protected function _cleanup()
    {

    }
    
    /**
     * {@inheritdoc}
     */
    protected function _hydrateAll()
    {
        $result = array();
        
        $data = $this->_result->getData();
        
        if(isset($data['response']['docs'])) {
            foreach($data['response']['docs'] as $row) {
                $this->_hydrateRow($row, $result);
            }
        }

        return $result;
    }

  
  
    /**
     * Hydrates a single row in a solr result set.
     * 
     * @internal
     * 
     * @param array $data The data of the row to process.
     * @param array $result The result array to fill.
     */
    protected function _hydrateRow(array $data, array &$result)
    {
        $row = array();
        
        foreach($data as $field => $value) {
            if($classMetadata->hasField($field)) {

                if($classMetadata->getFieldIsMulti($field)) {
                    $collection = new ArrayCollection();
                    
                    foreach((array) $value as $item) {
                        $collection->add($this->convertFieldTypeValue($item, $classMetadata->getTypeOfField($field)));
                    }

                    $row[$field] = $collection->toArray();
                } else {
                    $row[$field] = $this->convertFieldTypeValue($value, $classMetadata->getTypeOfField($field));
                }
            }
        }
        
        $result[] = $row;
    }
}

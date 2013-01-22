<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Hydration;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * The ObjectHydrator constructs an object graph out of a solr result set.
  */
class ObjectHydrator extends AbstractHydrator
{


    /**
     * {@inheritdoc}
     */
    protected function _cleanup()
    {
        parent::_cleanup();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _hydrateAll()
    {
        $results = array();
        
        $data = $this->_result->getData();

        if (isset($data['response']['docs'])) {
            foreach($data['response']['docs'] as $row) {
                $this->_hydrateRow($row, $results);
            }
        }

        return $results;
    }

    
    /**
     * Hydrates a single row in a solr result set.
     * 
     * @internal
     * 
     * @param array $data The data of the row to process.
     * @param array $results The results array to fill.
     */
    protected function _hydrateRow(array $data, array &$results)
    {
        
        $document = $this->_metadata->newInstance();
        
        foreach($data as $field => $value) {
            if($this->_metadata->hasField($field)) {

                if($this->_metadata->getFieldIsMulti($field)) {                            

                    $collection = new ArrayCollection();
                    foreach((array) $value as $item) {
                        $collection->add($this->convertFieldTypeValue($item, $this->_metadata->getTypeOfField($field)));
                    }

                    $this->_metadata->setFieldValue($document, $field, $collection);
                } else {
                    $this->_metadata->setFieldValue($document, $field, $this->convertFieldTypeValue($value, $this->_metadata->getTypeOfField($field)));
                }
            }
        }

        // add the document to the document map
        $repository = $this->_dm->getRepository($this->_metadata->getName());
        $repository->addDocumentToMap($document);
        
        $results[] = $document;
    }
    
    
    /**
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function convertFieldTypeValue($value, $type)
    {
        switch($type) {
            case "datetime":
                $value = new \DateTime($value);
                break;
            case "string":
            default:
                break;
        }
        
        return $value;
    }
}

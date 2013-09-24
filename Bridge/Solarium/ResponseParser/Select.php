<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium\ResponseParser;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Parse solr select response data
 *
 */
class Select extends Solarium\Client\ResponseParser
{

    
    /**
     * Get result data for the response
     *
     * @param Solarium_Result_Select $result
     * @return array
     */
    public function parse($result)
    {
        $data = $result->getData();
        $query = $result->getQuery();

        // create document instances
        $documentClass = $query->getOption('documentclass');
        
        $classMetadata = $query->getDocumentManager()->getClassMetadata($documentClass);
        
        $hydrator = $query->getDocumentManager()->getHydrator($query->getHydrationMode());
            
        $documents = $hydrator->hydrateAll($result, $classMetadata);
        
        // component results
        $components = array();
        $types = $query->getComponentTypes();
        foreach ($query->getComponents() as $component) {
            $componentParserClass = $types[$component->getType()]['responseparser'];
            if (!empty($componentParserClass)) {
                $componentParser = new $componentParserClass;
                $components[$component->getType()] = $componentParser->parse($query, $component, $data);
            }
        }

        if (isset($data['response']['numFound'])) {
            $numFound = $data['response']['numFound'];
        } else {
            $numFound = null;
        }
        
        return array(
            'status' => $data['responseHeader']['status'],
            'queryTime' => $data['responseHeader']['QTime'],
            'numfound' => $numFound,
            'documents' => $documents,
            'components' => $components,
        );
    }
    
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
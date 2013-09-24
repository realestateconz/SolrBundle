<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium\RequestBuilder;

use Realestate\SolrBundle\Bridge\Solarium\Client\Request;

class Update extends \Solarium\Client\RequestBuilder\Update
{
    protected $dm;
    
    public function setDocumentManager($dm)
    {
        $this->dm = $dm;
    }
    
    /**
     * Build request for an update query
     *
     * @param Solarium\Query\Update $query
     * @return \Realestate\SolrBundle\Bridge\Solarium\Client\Request
     */
    public function build($query)
    {
        $request = new Request;
        $request->setHandler($query->getHandler());
        $request->setMethod(Request::METHOD_POST);
        $request->addParam('wt', 'json');
        $request->setRawData($this->getRawData($query));
        $request->setContentType('text/xml');
        
        return $request;
    }
    
    
    /**
     * Build XML for an add command
     *
     * @param Solarium\Query\Update\Command\Add $command
     * @return string
     */
    public function buildAddXml($command)
    {

        $xml = '<add';
        $xml .= $this->boolAttrib('overwrite', $command->getOverwrite());
        $xml .= $this->attrib('commitWithin', $command->getCommitWithin());
        $xml .= '>';
        
        $dateFormat = 'Y-m-d\TH:i:s.u\Z';
        $utcTimeZone = new \DateTimeZone('UTC');

        foreach ($command->getDocuments() as $doc) {
            $xml .= '<doc>';

            $class = $this->dm->getClassMetadata(get_class($doc));
            
            foreach ($class->fieldMappings as $name => $metadata) {
                $value = $class->getFieldProcessedValue($doc, $name);
                if(null === $value) {
                    
                } elseif (is_array($value)) {
                    foreach ($value as $multival) {
                        $xml .= $this->_buildFieldXml($name, $metadata['boost'], $multival);
                    }
                } elseif(is_object($value)) {
                    if($value instanceof \DateTime) {
                        $value = $value->setTimezone($utcTimeZone)->format($dateFormat);
                    } else {
                        $value = (string) $value;
                    }
                    
                    $xml .= $this->_buildFieldXml($name, $metadata['boost'], $value);
                } else {
                    $xml .= $this->_buildFieldXml($name, $metadata['boost'], $value);
                }
            }

            $xml .= '</doc>';
        }

        $xml .= '</add>';
        
        //echo $xml; exit;

        return $xml;
    }
    
    
    /**
     * Build XML for a field
     *
     * Used in the add command
     *
     * @param string $name
     * @param float $boost
     * @param mixed $value
     * @return string
     */
    protected function _buildFieldXml($name, $boost, $value)
    {
        $translate = array(
            chr(7) => '', // bell code
            chr(31) => '', // unit separator
        );

        $value = strtr($value, $translate);
        $xml = '<field name="' . $name . '"';
        $xml .= $this->attrib('boost', $boost);
        $xml .= '>' . htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8');
        $xml .= '</field>';

        return $xml;
    }
}
<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium\Client;

class Request extends \Solarium\Client\Request
{
    protected $conten_type;

    /**
     * Request GET method
     */
    const METHOD_GET     = 'GET';

    /**
     * Request POST method
     */
    const METHOD_POST    = 'POST';

    /**
     * Request HEAD method
     */
    const METHOD_HEAD    = 'HEAD';

    
    public function setContentType($conten_type)
    {
        $this->conten_type = $conten_type;
    }
    
    public function getContentType()
    {
        return $this->conten_type;
    }
}
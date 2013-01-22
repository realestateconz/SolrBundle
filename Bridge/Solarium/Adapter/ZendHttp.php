<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium\Adapter;

use Realestate\SolrBundle\Bridge\Solarium\Client\Request;

/**
 * Adapter that uses a Zend_Http_Client
 *
 */
class ZendHttp extends \Solarium_Client_Adapter_ZendHttp 
{

    /**
     * Execute a Solr request using the Zend_Http_Client instance
     *
     * @param Realestate\SolrBundle\Bridge\Solarium\Client\Request $request
     * @return Realestate\SolrBundle\Bridge\Solarium\Client\Request
     */
    public function execute($request)
    {
        $client = $this->getZendHttp();

        $client->setMethod($request->getMethod());
        
        // Zend_Http_Client forces Content-Type to "application/x-www-form-urlencoded" for POST requests :(
        if(null !== $request->getContentType()) {
            $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, $request->getContentType());
        }
        
        
        $client->setUri($this->getBaseUri() . $request->getUri());
        $client->setRawData($request->getRawData());
        
        $client->setHeaders($request->getHeaders());

        $response = $client->request();

        // throw an exception in case of a HTTP error
        if ($response->isError()) {
            throw new \Solarium_Client_HttpException(
                $response->getMessage(),
                $response->getStatus()
            );
        }

        if ($request->getMethod() == Request::METHOD_HEAD) {
            $data = '';
        } else {
            $data = $response->getBody();
        }

        // this is used because getHeaders doesn't return the HTTP header...
        $headers = explode("\n", $response->getHeadersAsString());

        return new \Solarium_Client_Response($data, $headers);
    }
    
    
    /**
     * Get the Zend_Http_Client instance
     *
     *
     * @return \Zend_Http_Client
     */
    public function getZendHttp()
    {
        if (null == $this->_zendHttp) {
            $this->_zendHttp = new \Zend_Http_Client(null, $this->_options);
        }

        return $this->_zendHttp;
    }

}
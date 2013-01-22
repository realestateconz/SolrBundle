<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium;

use Realestate\SolrBundle\Bridge\Solarium\Client\Request;

class Client extends \Solarium_Client
{
    protected $dm;
    
    
    /**
     * Querytype mappings
     *
     * These can be customized using {@link registerQueryType()}
     */
    protected $_queryTypes = array(
        self::QUERYTYPE_SELECT => array(
            'query'          => '\Realestate\SolrBundle\Bridge\Solarium\Query\Select',
            //'query'          => '\Solarium_Query_Select',
            //'requestbuilder' => '\Solarium_Client_RequestBuilder_Select',
            'requestbuilder' => '\Realestate\SolrBundle\Bridge\Solarium\RequestBuilder\Select',
            'responseparser' => '\Realestate\SolrBundle\Bridge\Solarium\ResponseParser\Select'
        ),
        self::QUERYTYPE_UPDATE => array(
            'query'          => '\Realestate\SolrBundle\Bridge\Solarium\Query\Update',
            'requestbuilder' => '\Realestate\SolrBundle\Bridge\Solarium\RequestBuilder\Update',
            'responseparser' => '\Solarium_Client_ResponseParser_Update'
        ),
        self::QUERYTYPE_PING => array(
            'query'          => '\Solarium_Query_Ping',
            'requestbuilder' => '\Solarium_Client_RequestBuilder_Ping',
            'responseparser' => '\Solarium_Client_ResponseParser_Ping'
        ),
        self::QUERYTYPE_MORELIKETHIS => array(
            'query'           => '\Solarium_Query_MoreLikeThis',
            'requestbuilder'  => '\Solarium_Client_RequestBuilder_MoreLikeThis',
            'responseparser'  => '\Solarium_Client_ResponseParser_MoreLikeThis'
        ),
    );

    
    
    public function setDocumentManager($dm)
    {
        $this->dm = $dm;
    }
    
    
    
    
    /**
     * Creates a request based on a query instance
     *
     * @param Solarium_Query $query
     * @return Solarium_Client_Request
     */
    public function createRequest($query)
    {
        $queryType = $query->getType();
        
        if($queryType !== 'update') {
            return parent::createRequest($query);
        }
        

        $requestBuilder = $this->_queryTypes[$queryType]['requestbuilder'];
        if (is_string($requestBuilder)) {
            $requestBuilder = new $requestBuilder;
            $requestBuilder->setDocumentManager($this->dm);
        }

        $request = $requestBuilder->build($query);

        $this->_callPlugins('postCreateRequest', array($query, $request));

        return $request;
    }
    
    /**
     *
     * @param type $type
     * @param type $options
     * @return Solarium_Query
     */
    public function createQuery($type, $options = null)
    {
        $query = parent::createQuery($type, $options);
        
        $query->setDocumentManager($this->dm);
        
        return $query;
    }
}

<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium;

use Realestate\SolrBundle\Bridge\Solarium\Client\Request;

class Client extends \Solarium\Client
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
            'requestbuilder' => '\Realestate\SolrBundle\Bridge\Solarium\RequestBuilder\Select',
            'responseparser' => '\Realestate\SolrBundle\Bridge\Solarium\ResponseParser\Select'
        ),
        self::QUERYTYPE_UPDATE => array(
            'query'          => '\Realestate\SolrBundle\Bridge\Solarium\Query\Update',
            'requestbuilder' => '\Realestate\SolrBundle\Bridge\Solarium\RequestBuilder\Update',
            'responseparser' => '\Solarium\Client\ResponseParser\Update'
        ),
        self::QUERYTYPE_PING => array(
            'query'          => '\Solarium\Query\Ping',
            'requestbuilder' => '\Solarium\Client\RequestBuilder\Ping',
            'responseparser' => '\Solarium\Client\ResponseParser\Ping'
        ),
        self::QUERYTYPE_MORELIKETHIS => array(
            'query'           => '\Solarium\Query\MoreLikeThis',
            'requestbuilder'  => '\Solarium\Client\RequestBuilder\MoreLikeThis',
            'responseparser'  => '\Solarium\Client\ResponseParser\MoreLikeThis'
        ),
    );

    
    
    public function setDocumentManager($dm)
    {
        $this->dm = $dm;
    }
    
    
    
    
    /**
     * Creates a request based on a query instance
     *
     * @param Solarium\Query $query
     * @return Solarium\Client\Request
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
     * @return Solarium\Query
     */
    public function createQuery($type, $options = null)
    {
        $query = parent::createQuery($type, $options);
        
        $query->setDocumentManager($this->dm);
        
        return $query;
    }
}

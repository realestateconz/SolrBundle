<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium;

/**
 * A wrapper around Solarium\Client
 *
 */
class Connection
{

    /**
     * The wrapped Solarium client.
     *
     */
    protected $_client;

    /**
     * @var Realestate\SolrBundle\Bridge\Configuration
     */
    protected $_config;


    /**
     * Whether or not a connection has been established.
     *
     * @var boolean
     */
    private $_isConnected = false;


    /**
     * The parameters used during creation of the Connection instance.
     *
     * @var array
     */
    private $_params = array();
    

    /**
     * Initializes a new instance of the Connection class.
     *
     */
    public function __construct(array $params)
    {
     
        $this->_params = $params;
    }
    
    /**
     * Gets the parameters used during instantiation.
     *
     * @return Solarium\Client
     */
    public function getClient()
    {
        if(!$this->isConnected()) {
            $this->connect();
        }
        
        return $this->_client;
    }
    
    
    public function switchCore($core)
    {
        $this->getClient()->getAdapter()->setCore($core);
        
        return $this;
    }

    /**
     * Gets the parameters used during instantiation.
     *
     * @return array $params
     */
    public function getParams()
    {
        return $this->_params;
    }

    
    /**
     * Gets the hostname of the currently connected database.
     * 
     * @return string
     */
    public function getHost()
    {
        return isset($this->_params['options']['host']) ? $this->_params['options']['host'] : null;
    }
    
    /**
     * Gets the port of the currently connected database.
     * 
     * @return mixed
     */
    public function getPort()
    {
        return isset($this->_params['options']['port']) ? $this->_params['options']['port'] : null;
    }
    
    
    /**
     * Gets the port of the currently connected database.
     * 
     * @return mixed
     */
    public function getPath()
    {
        return isset($this->_params['options']['path']) ? $this->_params['options']['path'] : null;
    }
    
    /**
     * Gets the port of the currently connected database.
     * 
     * @return mixed
     */
    public function getAdapter()
    {
        return isset($this->_params['adapter']) ? $this->_params['adapter'] : 'HttpAdapter';
    }
    

    /**
     * Gets the Configuration used by the Connection.
     *
     * @return Doctrine\DBAL\Configuration
     */
    public function getConfiguration()
    {
        return $this->_config;
    }

    
    /**
     * Establishes the connection with the database.
     *
     * @return boolean TRUE if the connection was successfully established, FALSE if
     *                 the connection is already open.
     */
    public function connect()
    {
        if ($this->_isConnected) return false;
                
        $options = array(
            'adapter' => $this->getAdapter(),
            'adapteroptions' => isset($this->_params['options']) ? $this->_params['options'] : array(),
        );
        
        $this->_client = new \Realestate\SolrBundle\Bridge\Solarium\Client($this->getParams());
        
        $this->_isConnected = true;

        return true;
    }

    

    /**
     * Whether an actual connection to the database is established.
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->_isConnected;
    }


    /**
     * Closes the connection.
     *
     * @return void
     */
    public function close()
    {
        unset($this->_client);
        
        $this->_isConnected = false;
    }



}
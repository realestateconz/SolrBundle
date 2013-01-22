<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */
 

namespace Realestate\SolrBundle\Connection;


/**
 * Connection
 */
class Factory
{
 
    private $initialized = false;
    
    private $connectionClass;

    /**
     * Construct.
     *
     * @param array $typesConfig
     */
    public function __construct($connectionClass)
    {
        $this->connectionClass = $connectionClass;
    }

    /**
     * Create a connection by name.
     *
     * @param array         $params
     *
     * @return 
     */
    public function createConnection(array $params)
    {
        if (!$this->initialized) {
            $this->initialized = true;
        }
        

        $connection = new $this->connectionClass($params);
        

        return $connection;
    }


}

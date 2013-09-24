<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Tests\Mocks;

/**
 * Mock Solarium result
 */
class SolariumResultMock extends \Solarium\Result
{
    
    public function __construct()
    {
    }
    
    public function setData($data)
    {
        $this->_data = $data;
    }
    
    public function getData()
    {
        return $this->_data;
    }
}
<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Tests\ODM\Hydration;

use Realestate\ApiBundle\Test\ApiTestCase;
use Realestate\SolrBundle\ODM\Hydration\ObjectHydrator;


use Realestate\SolrBundle\Tests\Mocks\SolariumResultMock;

/**
 * 
 */
class ObjectHydratorTest extends ApiTestCase
{


    protected function setUp()
    {
        $bundle = new \Realestate\SolrBundle\RealestateSolrBundle();
        
        $bundle->boot();
    }
    
    
    public function testHydrateAll()
    {
        $dm = $this->_getTestDocumentManager();
        
        $hydrator = new ObjectHydrator($dm);
        $result = $this->getSolrResult();
        
        $classMetadata = $dm->getClassMetadata('Realestate\\ApiBundle\\Document\\ListingDenorm');
        
        $hydratedObjects = $hydrator->hydrateAll($result, $classMetadata);
        
        $this->assertInternalType('array', $hydratedObjects);
        $this->assertEquals(10, count($hydratedObjects));
        $this->assertContainsOnly('Realestate\\ApiBundle\\Document\\ListingDenorm', $hydratedObjects);
    }
    
    public function testHydrateRow()
    {
        $dm = $this->_getTestDocumentManager();
        $hydrator = new ObjectHydrator($dm);

        $classMetadata = $dm->getClassMetadata('Realestate\\ApiBundle\\Document\\ListingDenorm');
        
        $data = file_get_contents(__DIR__ . '/../../data/listings_solr_response.json');
        
        $hydratedObject = $hydrator->hydrateRow($result, $classMetadata);
    }
    
    
    protected function getSolrResult()
    {
        $result = new SolariumResultMock();
        
        $data = file_get_contents(__DIR__ . '/../../data/listings_solr_response.json');
        
        $result->setData(json_decode($data, true));
        
        return $result;
    }
    
    

}

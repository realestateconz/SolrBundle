<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * The DocumentRepository class
 */
class DocumentRepository /* implements ObjectRepository */
{
    /**
     * The database connection used by the DocumentManager.
     *
     * @var Realestate\SolrBundle\Bridge\Solarium\Connection
     */
    protected $dm;
    
    protected $documentName;
    
    protected $class;
    
    protected $documents = array();

 
    /**
     * Creates a new EntityManager that operates on the given database connection
     * and uses the given Configuration and EventManager implementations.
     *
     * @param DocumentManager $em The DocumentManager to use.
     */
    public function __construct($dm, Mapping\ClassMetadata $class)
    {
        $this->documentName = $class->name;
        $this->dm = $dm;
        $this->class = $class;
    }

    /**
     * @return string
     */
    protected function getDocumentName()
    {
        return $this->documentName;
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        $this->dm->getConnection()->switchCore($this->class->solrCore);
        return $this->dm;
    }
    
    /**
     * @return DocumentManager
     */
    public function getConnection()
    {
        return $this->getDocumentManager()->getConnection();
    }
    
    /**
     * Create a new Query\Builder instance that is prepopulated for this document name
     *
     * @return Query\Builder $qb
     */
    public function createQueryBuilder()
    {
        return $this->dm->createQueryBuilder($this->documentName);
    }
    
    
    /**
     * Finds an document by its primary key / identifier.
     *
     * @param $id The identifier.
     * @return object The document.
     */
    public function find($id)
    {
        if(null === $id) {
            return null;
        }
        
        // Check identity map first
        if(isset($this->documents[$id])) {
            return $this->documents[$id];
        }
        
        // make a solr call
        $qb = $this->createQueryBuilder();
        
        $qb->andWhere($qb->expr()->in($this->class->getIdentifier(), $id));
        
        $result = $this->getConnection()->getClient()->select($qb->getQuery());
        $documents = $result->getDocuments();
        
        if(is_array($id)) {
            return $documents;
        }
        
        return isset($documents[0]) ? $documents[0] : null;
    }
    
    /**
     * Add document to the document map
     * 
     * @param type $document 
     */
    public function addDocumentToMap($document)
    {
        $id = $this->class->getFieldValue($document, $this->class->getIdentifier());
        $this->documents[$id] = $document;
    }
    
    /**
     * Clears the repository, causing all managed entities to become detached.
     */
    public function clear()
    {
        $this->documents = array();
    }
   
}

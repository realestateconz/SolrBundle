<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM;

use Closure, Exception,
    Realestate\SolrBundle\Bridge\Solarium\Connection,
    Realestate\SolrBundle\ODM\Mapping\ClassMetadata,
    Realestate\SolrBundle\ODM\Configuration
    ;

/**
 * The DocumentManager is the central access point to solr documents
 */
class DocumentManager /*implements ObjectManager*/
{
    /**
     * The used Configuration.
     *
     * @var \Realestate\SolrBundle\ODM\Configuration
     */
    private $config;

    /**
     * The database connection used by the DocumentManager.
     *
     * @var \Realestate\SolrBundle\Bridge\Solarium\Connection
     */
    private $conn;

    /**
     * The EntityRepository instances.
     *
     * @var array
     */
    private $repositories = array();

    /**
     * The UnitOfWork used to coordinate object-level transactions.
     *
     * @var Solarium\Query\Update
     */
    private $unitOfWork;


    /**
     * Whether the EntityManager is closed or not.
     *
     * @var bool
     */
    private $closed = false;
    
    /**
     * The expression builder instance used to generate query expressions.
     *
     * @var Realestate\SolrBundle\ODM\Query\Expr
     */
    private $expressionBuilder;
    
    /**
     * Hydrators cache
     */
    protected $hydrators = array();

    /**
     * Creates a new EntityManager that operates on the given database connection
     * and uses the given Configuration and EventManager implementations.
     *
     * @param Realestate\SolrBundle\Bridge\Solarium\Connection $conn
     * @param Realestate\SolrBundle\ODM\Configuration $config
     */
    public function __construct(Connection $conn, Configuration $config = null)
    {
        $this->conn = $conn;
        $this->conn->getClient()->setDocumentManager($this);
        $this->config = $config;
        $this->metadataFactory = new Mapping\ClassMetadataFactory();
        $this->metadataFactory->setDocumentManager($this);
        
        if(null !== $config) {
            $this->metadataFactory->setCacheDriver($this->config->getMetadataCacheImpl());
        }
        
    }

    /**
     * Gets the database connection object used by the EntityManager.
     *
     * @return Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->conn;
    }
    
    
    /**
     * Gets the metadata factory used to gather the metadata of classes.
     *
     * @return Realestate\SolrBundle\ODM\Mapping\ClassMetadataFactory
     */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

  
   
    /**
     * Commits a transaction on the underlying database connection.
     *
     * @deprecated Use {@link getConnection}.commit().
     */
    public function commit()
    {
        $this->conn->commit();
    }

    /**
     * Performs a rollback on the underlying database connection.
     *
     * @deprecated Use {@link getConnection}.rollback().
     */
    public function rollback()
    {
        $this->conn->rollback();
    }

   
    /**
     * Create a QueryBuilder instance
     *
     * @return QueryBuilder $qb
     */
    public function createQueryBuilder($documentName)
    {
        return new Query\Builder($this, $documentName);
    }

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     *
     */
    public function flush()
    {
        // commit changes to solr
        if(!is_null($this->unitOfWork)) {
            $this->unitOfWork->addCommit();
            $this->getConnection()->getClient()->update($this->unitOfWork);
            $this->unitOfWork = null;
        }
        
        // clear the managed documents
        foreach($this->repositories as $repository) {
            $repository->clear();
        }
    }

    /**
     * Finds a Document by its identifier.
     *
     * This is just a convenient shortcut for getRepository($entityName)->find($id).
     *
     * @param string $entityName
     * @param mixed $identifier
     * @return object
     */
    public function find($entityName, $identifier)
    {
        return $this->getRepository($entityName)->find($identifier);
    }

 


    /**
     * Clears the EntityManager. All entities that are currently managed
     * by this EntityManager become detached.
     *
     * @param string $entityName
     */
    public function clear($entityName = null)
    {
        if ($entityName === null) {
            $this->unitOfWork->clear();
        } else {
            //TODO
            throw new ODMException("DocumentManager#clear(\$entityName) not yet implemented.");
        }
    }

    /**
     * Closes the EntityManager. All entities that are currently managed
     * by this EntityManager become detached. The EntityManager may no longer
     * be used after it is closed.
     */
    public function close()
    {
        $this->clear();
        $this->closed = true;
    }

    /**
     * Tells the EntityManager to make an instance managed and persistent.
     *
     * The entity will be entered into the database at or before transaction
     * commit or as a result of the flush operation.
     * 
     * NOTE: The persist operation always considers entities that are not yet known to
     * this EntityManager as NEW. Do not pass detached entities to the persist operation.
     *
     * @param object $object The instance to make managed and persistent.
     */
    public function persist($entity)
    {
        if ( ! is_object($entity)) {
            throw new \InvalidArgumentException(gettype($entity));
        }
        
        $this->getUnitOfWork()->addDocuments(array($entity));
    }

    protected function getUnitOfWork()
    {
        if(is_null($this->unitOfWork)) {
            $this->unitOfWork = $this->getConnection()->getClient()->createUpdate();
        }
  
        return $this->unitOfWork;
    }
  
    /**
     * Gets the repository for an entity class.
     *
     * @param string $documentName The name of the entity.
     * @return DocumentRepository The repository class.
     */
    public function getRepository($documentName)
    {
        if (isset($this->repositories[$documentName])) {
            return $this->repositories[$documentName];
        }

       
        $metadata = $this->getClassMetadata($documentName);
        $customRepositoryClassName = $metadata->customRepositoryClassName;
        
        
        if ($customRepositoryClassName !== null) {
            
            $repository = new $customRepositoryClassName($this, $metadata);
        } else {
            $repository = new DocumentRepository($this, $metadata);
        }

        $this->repositories[$documentName] = $repository;

        return $repository;
    }
    
    /**
     *
     * @param string $documentName
     * @return type 
     */
    public function getClassMetadata($documentName)
    {
        return $this->metadataFactory->getMetadataFor($documentName);
    }


    /**
     * Gets the Configuration used by the EntityManager.
     *
     * @return Realestate\SolrBundle\ODM\Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }

   

    /**
     * Check if the Entity manager is open or closed.
     * 
     * @return bool
     */
    public function isOpen()
    {
        return (!$this->closed);
    }

    /**
     *
     * @return type Query\Expr
     */
    public function getExpressionBuilder()
    {
        if ($this->expressionBuilder === null) {
            $this->expressionBuilder = new Query\Expr;
        }
        
        return $this->expressionBuilder;
    }
    
    
    /**
     * Gets a hydrator for the given hydration mode.
     *
     * This method caches the hydrator instances which is used for all queries that don't
     * selectively iterate over the result.
     *
     * @param string $hydrationMode
     * @return Realestate\SolrBundle\ODM\Hydration\AbstractHydrator
     */
    public function getHydrator($hydrationMode)
    {
        if ( ! isset($this->hydrators[$hydrationMode])) {
            $this->hydrators[$hydrationMode] = $this->newHydrator($hydrationMode);
        }

        return $this->hydrators[$hydrationMode];
    }
    
    /**
     * Create a new instance for the given hydration mode.
     *
     * @param  string $hydrationMode
     * @return Realestate\SolrBundle\ODM\Hydration\AbstractHydrator
     */
    public function newHydrator($hydrationMode)
    {
        switch ($hydrationMode) {
            case 'Array':
                $hydrator = new Hydration\SingleScalarHydrator($this);
                break;
            case 'Object':
            default:
                $hydrator = new Hydration\ObjectHydrator($this);
                break;
        }

        return $hydrator;
    }
    
    
    
    
    /**
     * Factory method to create DocumentManager instances.
     *
     * @param mixed $conn An array with the connection parameters or an existing
     *      Connection instance.
     * @param Configuration $config The Configuration instance to use.
     * @return DocumentManager The created DocumentManager.
     */
    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {

        return new DocumentManager($conn, $config);
    }

   
}

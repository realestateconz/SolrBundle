<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Tools;

use Realestate\SolrBundle\ODM\ODMException,
    Realestate\SolrBundle\ODM\DocumentManager,
    Realestate\SolrBundle\ODM\Mapping\ClassMetadata;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * 
 */
class SchemaTool
{
    /**
     * @var \Realestate\SolrBundle\ODM\DocumentManager
     */
    private $_dm;
    
    
    /**
     *
     * @var array
     */
    private $schemas = array();

   /**
     * Initializes a new SchemaTool instance that uses the connection of the
     * provided DocumentManager.
     *
     * @param Realestate\SolrBundle\ODM\DocumentManager $em
     */
    public function __construct(DocumentManager $dm)
    {
        $this->_dm = $dm;
    }

    /**
     * Creates the database schema for the given array of ClassMetadata instances.
     *
     * @param array $classes
     */
    public function createSchema(array $classes)
    {
        $createSchemaXml = $this->getCreateSchema($classes);
        
        // @todo - save to disk
    }

    /**
     *
     * @param array $classes
     * @return array $sql The SQL statements needed to create the schema for the classes.
     */
    public function getCreateSchema(array $classes)
    {
        $schema = $this->getSchemaFromMetadata($classes);
        return $schema->toSql();
    }


    /**
     * From a given set of metadata classes this method creates a Schema instance.
     *
     * @param array $classes
     * @return Schema
     */
    public function getSchemaFromMetadata($class)
    {
        $class = $this->_dm->getClassMetadata($class);
        

        $schema = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?>
<schema name="' . $class->solrCore . ' schema" version="1.4">
<types>
<fieldType name="string" class="solr.StrField" omitNorms="true"/>
    
<!-- boolean type: "true" or "false" -->
<fieldType name="boolean" class="solr.BoolField" omitNorms="true"/>

<!--
Default numeric field types. For faster range queries, consider the tint/tfloat/tlong/tdouble types.
-->
<fieldType name="int" class="solr.IntField"  omitNorms="true"/>
<fieldtype name="integer" class="solr.IntField"  omitNorms="true"/>

<fieldType name="float" class="solr.TrieFloatField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>
<fieldType name="long" class="solr.TrieLongField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>
<fieldType name="double" class="solr.TrieDoubleField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>

<!--
Numeric field types that index each value at various levels of precision to accelerate range queries when the number of values between the range endpoints is large. See the javadoc for NumericRangeQuery for internal implementation details. Smaller precisionStep values (specified in bits) will lead to more tokens indexed per value, slightly larger index size, and faster range queries. A precisionStep of 0 disables indexing at different precision levels.
-->
<fieldType name="tint" class="solr.TrieIntField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>
<fieldType name="tfloat" class="solr.TrieFloatField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>
<fieldType name="tlong" class="solr.TrieLongField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>
<fieldType name="tdouble" class="solr.TrieDoubleField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>

<!--
The format for this date field is of the form 1995-12-31T23:59:59Z, and is a more restricted form of the canonical representation of dateTime http://www.w3.org/TR/xmlschema-2/#dateTime The trailing "Z" designates UTC time and is mandatory. Optional fractional seconds are allowed: 1995-12-31T23:59:59.999Z All other components are mandatory. Expressions can also be used to denote calculations that should be performed relative to "NOW" to determine the value, ie... NOW/HOUR ... Round to the start of the current hour NOW-1DAY ... Exactly 1 day prior to now NOW/DAY+6MONTHS+3DAYS ... 6 months and 3 days in the future from the start of the current day Consult the DateField javadocs for more information. Note: For faster range queries, consider the tdate type
-->
<fieldType name="date" class="solr.TrieDateField" omitNorms="true" precisionStep="0" positionIncrementGap="0"/>
<fieldType name="datetime" class="solr.TrieDateField" omitNorms="true" precisionStep="0" positionIncrementGap="0"/>
<!--
A Trie based date field for faster date range queries and date faceting.
-->
<fieldType name="tdate" class="solr.TrieDateField" omitNorms="true" precisionStep="6" positionIncrementGap="0"/>

<!--
A specialized field for geospatial search. If indexed, this fieldType must not be multivalued.
-->
<fieldType name="location" class="solr.LatLonType" subFieldSuffix="_coordinate"/>

<!--
A Geohash is a compact representation of a latitude longitude pair in a single field. See http://wiki.apache.org/solr/SpatialSearch
-->
<fieldtype name="geohash" class="solr.GeoHashField"/>


<!--
A general text field that has reasonable, generic cross-language defaults: it tokenizes with StandardTokenizer, removes stop words from case-insensitive "stopwords.txt" (empty by default), and down cases. At query time only, it also applies synonyms.
-->
<fieldtype name="text"    class="solr.TextField"  positionIncrementGap="100">
 <analyzer type="index">
   <tokenizer class="solr.WhitespaceTokenizerFactory" /> 
   <filter class="solr.WordDelimiterFilterFactory" generateWordParts="1" generateNumberParts="1" catenateWords="1" catenateNumbers="1" catenateAll="0" splitOnCaseChange="1" /> 
   <filter class="solr.LowerCaseFilterFactory" /> 
   <filter class="solr.RemoveDuplicatesTokenFilterFactory" /> 
   <filter class="solr.StopFilterFactory" words="stopwords.txt" ignoreCase="true"/>
 </analyzer>
 <analyzer type="query">
   <tokenizer class="solr.WhitespaceTokenizerFactory" /> 
   <filter class="solr.LowerCaseFilterFactory" /> 
   <filter class="solr.WordDelimiterFilterFactory" generateWordParts="1" generateNumberParts="1" catenateWords="0" catenateNumbers="0" catenateAll="0" splitOnCaseChange="1" /> 
   <filter class="solr.RemoveDuplicatesTokenFilterFactory" /> 
   <filter class="solr.StopFilterFactory" words="stopwords.txt" ignoreCase="true"/>
 </analyzer>
</fieldtype>


<!--
A text field used to store xml data
-->
<fieldtype name="xml"    class="solr.TextField" omitNorms="true" positionIncrementGap="0"></fieldtype>

<!--
A text field used to store csv data
-->
<fieldtype name="csv"    class="solr.TextField" omitNorms="true" positionIncrementGap="0"></fieldtype>
</types>
  
<fields>
    <field name="row_uid" type="string" indexed="true" stored="true" multiValued="false" required="false"/>
    <field name="timestamp" type="date" indexed="true" stored="true" default="NOW" multiValued="false"/>
    
    <dynamicField name="*_coordinate"  type="tdouble" indexed="true"  stored="false"/>
</fields>
    
  <uniqueKey>row_uid</uniqueKey>
  <defaultSearchField>text</defaultSearchField>
  
   <copyField source="' . $class->identifier . '" dest="row_uid"/>
</schema>');

        
        foreach($class->fieldMappings as $field => $definition) {
            $xmlField = $schema->fields->addChild('field');
            $xmlField->addAttribute('name', $definition['fieldName']);
            $xmlField->addAttribute('type', $definition['type']);
            $xmlField->addAttribute('indexed', $definition['indexed'] ? 'true' : 'false');
            $xmlField->addAttribute('stored', $definition['stored'] ? 'true' : 'false');
            $xmlField->addAttribute('multiValued', $definition['multiValued'] ? 'true' : 'false');
            $xmlField->addAttribute('required', $definition['required'] ? 'true' : 'false');
            
            if(isset($definition['omitNorms'])) {
                $xmlField->addAttribute('omitNorms', $definition['omitNorms'] ? 'true' : 'false');
            }
            
        }
        
        return $schema;
    }

  
  

    public function getSqlUpdateQueryFromMetadata($class, $em)
    {
        
        $odmMetadata = $this->_dm->getClassMetadata($class);
        $ormMetadata = $em->getClassMetadata($odmMetadata->ormEntity);
        
        $qb = new QueryBuilder($em->getConnection());
        
        
        //var_dump($ormMetadata);exit;
        $selectFields = array();
        foreach($odmMetadata->fieldMappings as $name => $fieldMapping) {
            if(isset($fieldMapping['sqlExpr'])) {
                $columnName = $ormMetadata->getColumnName($name);

                $matches = array();
                if(preg_match('#^([a-z])\.([a-zA-Z_]+)$#', $fieldMapping['sqlExpr'], $matches)) {
                    // this is a reference to a field from a join, eg m.auction_date
                    $tableAlias = $matches[1];
                    $columnName = $matches[2];
                    $columnsSql = $this->getColumnSql($columnName, $name, $fieldMapping['type'], $tableAlias) . "\n";
                    
                } else {
                    // it is a full sql expression - leave it as is, eg SELECT oh.openhome_start_datetime AS s, oh.openhome_end_datetime AS e, oh.open_home_id AS i FROM RealestateData.dbo.listing_current_openhomes oh WHERE oh.listing_id = e.listing_id for xml AUTO
                    $columnsSql = '(' . $fieldMapping['sqlExpr'] . ') AS ' . $name . "\n";
                }
                
                
                
                $selectFields[] = $columnsSql;
            } elseif($ormMetadata->hasField($name)) {
                $columnName = $ormMetadata->getColumnName($name);

                $columnsSql = $this->getColumnSql($columnName, $name, $fieldMapping['type'], 'e') . "\n";
                
                $selectFields[] = $columnsSql;
            } elseif(isset($fieldMapping['dqlJoin'])) {
                if(isset($fieldMapping['sqlColumnName'])) {
                    $selectFields[] = $fieldMapping['dqlJoinAlias'] . '.' . $fieldMapping['sqlColumnName'] . ' AS ' . $name;
                } else {
                    $selectFields[] = $fieldMapping['dqlJoinAlias'] . '.' . $name;
                }
                
                if($fieldMapping['dqlJoinType'] == 'left') {
                    $qb->leftJoin($fieldMapping['dqlJoin'], $fieldMapping['dqlJoinAlias']);
                } else {
                    $qb->innerJoin($fieldMapping['dqlJoin'], $fieldMapping['dqlJoinAlias']);
                }
            } else {
                
            }
        }

        $qb
            ->select($selectFields)
            ->from($ormMetadata->table['name'], 'e')
        ;
        
        $query = $qb->getSQL();
        
        return $query;
    }
   
    protected function getColumnSql($columnName, $alias, $type, $tableAlias)
    {
        $columnSql = '';
        
        if($type == 'datetime') {
            $columnSql = "CONVERT(varchar(50), $tableAlias.$columnName, 126) + 'Z'"  . ' AS ' . $alias;
        } else {
            $columnSql = $tableAlias . '.' . $columnName;
            
            if($columnName !== $alias) {
                $columnSql = $columnSql . ' AS ' . $alias;
            }
        }
        
        
        
        
        return $columnSql;
    }
    
}

<?php

/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Query;

use Realestate\SolrBundle\ODM\DocumentManager;

/**
 * Query builder for Solr.
 *
 */
class Builder {

	protected $_queryParts = array(
		// by default select all fields plus the score
		'select' => '*,score',
		// 
		'query' => '*:*',
		'sort' => array()
	);
	protected $_geoParts = array();
	protected $_facetPart;
	protected $filterParts = array();
	protected $fieldParts = array();
	protected $maxResults = 10;
	protected $offset = 0;

	/**
	 * @var array The query parameters.
	 */
	private $_params = array();

	/**
	 * @var array The query sort columns.
	 */
	private $_sorts = array();

	/**
	 * The DocumentManager instance for this query
	 *
	 * @var DocumentManager
	 */
	private $dm;

	/**
	 * The ClassMetadata instance.
	 *
	 * @var ClassMetadata
	 */
	private $class;

	public function __construct(DocumentManager $dm, $documentName = null) {
		$this->dm = $dm;

		if ($documentName !== null) {
			$this->setDocumentName($documentName);
		}
	}

	public function rangeBy($expr) {
		if (!empty($this->queryParts)) {
			$this->queryParts[] = ' AND ';
		}
		$this->queryParts[] = '(' . $expr . ')';

		return $this;
	}

	/**
	 * @param $value
	 * @return Builder
	 */
	public function match($x, $value) {
		$value = trim($value, "\n\t\r\s\" \\')(%");

		if ($value === '') {
			return $this;
		}

		if (strripos($value, ' AND ') > 0) {
			$value = str_replace(' and ', ' AND ', $value);

			$subParts = explode(' AND ', $value);

			$this->queryParts[] = '(';

			foreach ($subParts as $subPart) {
				$this->match($subPart);
			}

			$this->queryParts[] = ')';
		} elseif (strripos($value, ' OR ') > 0) {
			$value = str_replace(' or ', ' OR ', $value);

			$subParts = explode(' OR ', $value);

			$this->queryParts[] = '(';

			$first = true;
			foreach ($subParts as $subPart) {
				if (!$first) {
					$this->exprOr();
				}
				$this->match($subPart);
				$first = false;
			}

			$this->queryParts[] = ')';
		} else {
			$this->queryParts[] = $x . ':"' . $this->escape($value) . '"';
		}


		return $this;
	}

	public function exprOr() {
		$this->queryParts[] = 'OR';

		return $this;
	}

	public function expr() {
		return $this->dm->getExpressionBuilder();
	}

	public function andWhere($expr) {
		if (!empty($this->filterParts)) {
			$this->filterParts[] = ' AND ';
		}

		if (!is_string($expr)) {
			var_dump($expr);
			exit;
		}

		$this->filterParts[] = '(' . $expr . ')';

		return $this;
	}

	public function orWhere($expr) {
		if (!empty($this->filterParts)) {
			$this->filterParts[] = ' OR ';
		}

		if (!is_string($expr)) {
			var_dump($expr);
			exit;
		}

		$this->filterParts[] = '(' . $expr . ')';

		return $this;
	}

	public function filterBy($field, $expr) {
		$this->fieldParts[$field] = $expr;

		return $this;
	}

	public function escape($value) {
		$match = array('\\', '+', '-', '&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '~', '*', '?', ':', '"', ';', ' ');
		$replace = array('\\\\', '\\+', '\\-', '\\&', '\\|', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\~', '\\*', '\\?', '\\:', '\\"', '\\;', '\\ ');
		$value = str_replace($match, $replace, $value);

		return $value;
	}

	public function setMaxResults($max) {
		if (!is_numeric($max)) {
			return false;
		}

		$this->maxResults = $max;

		return $this;
	}

	public function setFirstResult($offset) {
		$this->setOffset($offset);

		return $this;
	}

	public function setOffset($offset) {
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Gets the Query executable.
	 *
	 * @param array $options
	 * @return QueryInterface $query
	 */
	public function getQuery(array $options = array()) {
		$query = $this->dm->getConnection()->getClient()->createSelect();

		$query->setDocumentClass($this->class->getName());

		if (count($this->_geoParts)) {
			$query->setGeoDistance($this->_geoParts['distance']);
			$query->setGeoPoint($this->_geoParts['longitude'], $this->_geoParts['latitude']);
			$query->setGeoColumn($this->_geoParts['column']);

			if (!empty($this->filterParts)) {
				$this->filterParts[] = ' AND ';
			}

			$this->filterParts[] = '_query_:"{!geofilt}"';
		}


		// use for listing counts by region district suburb
		if ($this->_facetPart) {
			// TODO check facet field exists
			$facetSet = $query->getFacetSet();

			// otherwise we only get the first 100 facets back
			$facetSet->setLimit(-1);

			foreach ($this->_facetPart as $facet) {
				$facetSet->createFacetField($facet)->setField($facet);
			}
		}


		// create a filterquery
		if (count($this->filterParts) > 0) {
			$fq = $query->createFilterQuery();
			$fq->setKey('id');
			$fq->setQuery($this->renderFilterQuery());
			$query->addFilterQuery($fq);
		}


		// add hard filters         
		foreach ($this->fieldParts as $field => $expr) {
			$fq = $query->createFilterQuery();
			$fq->setKey($field);
			$fq->setQuery($expr);
			$query->addFilterQuery($fq);
		}

		/* add custom fq??
		  $cq = $query->createFilterQuery(); //->setQuery('region_id:50');
		  $cq->setKey('cq');
		  $cq->setQuery('farms_region_id:50 OR residential_region_id:50 OR commercial_region_id:50 OR business_region_id:50');
		  $query->addFilterQuery($cq);
		 * 
		 */

		// set query
		$query->setQuery($this->renderQuery());

		$query->setRows($this->maxResults);
		$query->setStart($this->offset);

		foreach ($this->_sorts as $column => $order) {
			$query->addSort($column, $order);
		}

		$query->setFields($this->renderSelect());

		return $query;
	}

	/**
	 *
	 * @return string
	 */
	public function renderQuery() {
		if (empty($this->queryParts)) {

			return $this->_queryParts['query'];

			// hack?
			//return '*:*';
		}

		return implode(' ', $this->expandParams($this->queryParts));
	}

	/**
	 *
	 * @return string
	 */
	public function renderSelect() {
		$select = isset($this->_queryParts['select']) ? implode(',', (array) $this->_queryParts['select']) : '*,score';

		return $select;
	}

	/**
	 *
	 * @param array $queryParts
	 * @return array 
	 */
	protected function expandParams(array $queryParts) {
		foreach ($queryParts as &$queryPart) {
			foreach ($this->_params as $paramName => $paramValue) {
				$tokenSearch = ':' . $paramName;

				if (strpos($queryPart, $tokenSearch) !== false) {
					$queryPart = str_replace($tokenSearch, $this->expr()->escape($paramValue), $queryPart);
				}
			}
		}

		return $queryParts;
	}

	public function renderFilterQuery() {
		if (empty($this->filterParts)) {
			return '*:*';
		}

		return implode(' ', $this->expandParams($this->filterParts));
	}

	private function setDocumentName($documentName) {
		if ($documentName !== null) {
			$this->class = $this->dm->getClassMetadata($documentName);
			if (null !== $this->class->solrCore) {
				$this->dm->getConnection()->switchCore($this->class->solrCore);
			}
		}
	}

	/**
	 * Specifies fields that is to be returned in the query result.
	 * Replaces any previously specified selections, if any.
	 *
	 *
	 * @param mixed $select The selection expressions.
	 * @return Builder This Builder instance.
	 */
	public function select($select = null) {
		if (empty($select)) {
			return $this;
		}

		$selects = is_array($select) ? $select : func_get_args();

		$this->_queryParts['select'] = $selects;
	}

	/**
	 * 
	 */
	public function addSelect($select) {
		$this->_queryParts['select'][] = $select;

		return $this;
	}

	/**
	 * Either appends to or replaces a single, generic query part.
	 *
	 * The available parts are: 'select', 'where' and 'orderBy'.
	 *
	 * @param string $solrPartName 
	 * @param string $queryPart 
	 * @param string $append 
	 * @return Builder This Builder instance.
	 */
	public function add($queryPartName, $queryPart, $append = false) {
		$isMultiple = is_array($this->_queryParts[$queryPartName]);

		if ($append && $isMultiple) {
			if (is_array($queryPart)) {
				$key = key($queryPart);

				$this->_queryParts[$queryPartName][$key][] = $queryPart[$key];
			} else {
				$this->_queryParts[$queryPartName][] = $queryPart;
			}
		} else {
			$this->_queryParts[$queryPartName] = ($isMultiple) ? $queryPart : array($queryPart);
		}

		return $this;
	}

	/**
	 * Sets a query parameter for the query being constructed.
	 *
	 * @param string|integer $key The parameter position or name.
	 * @param mixed $value The parameter value.
	 * @return QueryBuilder This QueryBuilder instance.
	 */
	public function setParameter($key, $value) {
		$this->_params[$key] = $value;

		return $this;
	}

	public function addOrderBy($column, $order = 'asc') {
		$order = strtolower($order);

		$this->_sorts[$column] = $order;

		return $this;
	}

	public function addGroupBy() {
		return $this;
	}

	public function addGeoFilter($column, $latitude, $longitude, $distance = 20) {
		$this->_geoParts['column'] = $column;
		$this->_geoParts['latitude'] = $latitude;
		$this->_geoParts['longitude'] = $longitude;
		$this->_geoParts['distance'] = $distance;
	}

	public function addFacetPart($string) {
		$this->_facetPart = $string;
	}

}

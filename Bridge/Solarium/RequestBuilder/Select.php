<?php
/**
 * 
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\Bridge\Solarium\RequestBuilder;

use Realestate\SolrBundle\Bridge\Solarium\Client\Request;

/**
 * Build a select request
 *
 * @package Solarium
 * @subpackage Client
 */
class Select extends \Solarium\Client\RequestBuilder\Select
{

    /**
     * Build request for a select query
     *
     * @param Solarium\Query\Select $query
     * @return Realestate\SolrBundle\Bridge\Solarium\Client\Request
     */
    public function build($query)
    {
        $request = new Request;
        $request->setHandler($query->getHandler());
        $request->setMethod(Request::METHOD_POST);
        $request->addParam('wt', 'json');
        $request->setRawData($this->getRawData($query));
        $request->addHeader('Content-Type: application/x-www-form-urlencoded');
        
        
        //setHeaders
        
        // add basic params to request
        
        // add components to request
        $types = $query->getComponentTypes();
        foreach ($query->getComponents() as $component) {
            $componentBuilderClass = $types[$component->getType()]['requestbuilder'];
            if (!empty($componentBuilderClass)) {
                $componentBuilder = new $componentBuilderClass;
                $request = $componentBuilder->build($component, $request);
            }
        }
        
        return $request;
    }
    
    
    
    public function getRawData($query)
    {
        $request = new Request;
        $request->setHandler('');
        
        // add basic params to request
        $request->addParam('q', $query->getQuery());
        $request->addParam('start', $query->getStart());
        $request->addParam('rows', $query->getRows());
        $request->addParam('fl', implode(',', $query->getFields()));

        if ($query->getGeoDistance()) $request->addParam('d', $query->getGeoDistance());
        if ($query->getGeoPoint()) $request->addParam('pt', $query->getGeoPoint());
        if ($query->getGeoColumn()) $request->addParam('sfield', $query->getGeoColumn());

        // add sort fields to request
        $sort = array();
        foreach ($query->getSorts() AS $field => $order) {
            $sort[] = $field . ' ' . $order;
        }
        if (count($sort) !== 0) {
            $request->addParam('sort', implode(',', $sort));
        }

        // add filterqueries to request
        $filterQueries = $query->getFilterQueries();
        if (count($filterQueries) !== 0) {
            foreach ($filterQueries AS $filterQuery) {
                $fq = $this->renderLocalParams(
                    $filterQuery->getQuery(),
                    array('tag' => $filterQuery->getTags())
                );
                $request->addParam('fq', $fq);
            }
        }
        
        return ltrim($request->getUri(), '?');
    }

}

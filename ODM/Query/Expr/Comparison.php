<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Query\Expr;



/**
 * Expression class for DQL comparison expressions
 *
 */
class Comparison
{
    const EQ  = '=';
    const NEQ = '<>';
    const LT  = '<';
    const LTE = '<=';
    const GT  = '>';
    const GTE = '>=';
    
    private $_leftExpr;
    private $_operator;
    private $_rightExpr;

    public function __construct($leftExpr, $operator, $rightExpr)
    {
        $this->_leftExpr  = $leftExpr;
        $this->_operator  = $operator;
        $this->_rightExpr = $rightExpr;
    }

    public function __toString()
    {
        return $this->_leftExpr . ' ' . $this->_operator . ' ' . $this->_rightExpr;
    }
}
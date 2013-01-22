<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM\Query;

class Expr
{
    
    public function eq($x, $y)
    {
        return $x . ':"' . $this->escape($y) . '"';
    }
    
    public function neq($x, $y)
    {
        return '-' . $x . ':"' . $this->escape($y) . '"';
    }
    
    public function lt($x, $y)
    {
        return $x . ':[* TO ' . $this->escape($y) . ']';
    }
    
    public function lte($x, $y)
    {
        return $x . ':[* TO ' . $this->escape($y) . ']';
    }
    
    public function gt($x, $y)
    {
        return $x . ':[' . $this->escape($y) . ' TO *]';
    }
    
    public function gte($x, $y)
    {
        return $x . ':[' . $this->escape($y) . ' TO *]';
    }
    
    public function between($x, $y, $z)
    {
        return $x . ':[' . $this->escape($y) . ' TO ' . $this->escape($z) . ']';
    }
    
    public function in($x, $y)
    {
        if(!is_array($y)) {
            $y = (array) $y;
        }
        
        $expr = '';
        
        if(count($y) > 0) {
            $expr = $x . ':(' . implode(' OR ', $y) . ')';
        }
        

        return $expr;
    }
    
    public function notIn($x, $y)
    {
        if(!is_array($y)) {
            $y = (array) $y;
        }
        
        $expr = '';
        
        if(count($y) > 0) {
            $expr = '-' . $x . ':(' . implode(' AND ', $y) . ')';
        }
        

        return $expr;
    }
    
    public function escape($value)
    {
        if($value instanceof \DateTime) {
            $value = $value->format('Y-m-d\TH:i:s.u\Z');
        }
        
        $match = array('\\', '+', '-', '&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '~', '?', ':', '"', ';', ' ');
        $replace = array('\\\\', '\\+', '\\-', '\\&', '\\|', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\~', '\\?', '\\:', '\\"', '\\;', '\\ ');
        $value = str_replace($match, $replace, $value);
 
        return $value;
    }


    public function bbox($column, $x1, $y1, $x2, $y2)
    {
        $expr = $column . ':[' . $x1 . ',' . $y1 . ' TO ' . $x2 . ',' . $y2 . ']';

        return $expr;
    }
   
    public function geofilt($column, $latitude, $longitude, $distance = 20)
    {
        $expr = '{!geofilt pt=' . $latitude . ',' . $longitude . ' sfield=' . $column . ' d=' . $distance . '}';

        return $expr;
    } 
    
    
    public function match($x, $value)
    {
        $value = trim($value, "\n\t\r\s\" \\')(%");
        
        $expr = '';
        
        if($value === '') {
            return '';
        }
        
        if(strripos($value, ' AND ') > 0) {
            $value = str_replace(' and ', ' AND ', $value);
            
            $subParts = explode(' AND ', $value);
            
            $expr = '(';
            
            foreach($subParts as $subPart) {
                $expr .= $this->match($subPart);
            }
            
            $expr .= ')';
        } elseif(strripos($value, ' OR ') > 0) {
            $value = str_replace(' or ', ' OR ', $value);
            
            $subParts = explode(' OR ', $value);
            
            $expr .= '(';
            
            $first = true;
            foreach($subParts as $subPart) {
                if(!$first) {
                    $expr .= ' OR ';
                }
                $expr .= $this->match($subPart);
                $first = false;
            }
            
            $expr .= ')';
        } else {
            $expr .=  $x . ':"' . $this->escape($value) . '"';
        }
        
        return $expr;
    }
    
    public function matchWildcard($x, $y)
    {
        
    }
    
}

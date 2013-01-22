<?php
/**
 * Copyright 2012 Realestate.co.nz Ltd
 *
 * @author      Ken Golovin <ken@webplanet.co.nz>
 */

namespace Realestate\SolrBundle\ODM;

/**
 * 
 */
interface Document
{
    public function getBoost();
    
    public function getScore();
}
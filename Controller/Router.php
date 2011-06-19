<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in 
 * the project root's LICENSE file. 
 */
/**
 * Lily_Controller_Router class.
 * @author Matt Ward
 */
class Lily_Controller_Router 
{
    private $_routes;
    
    public function __construct($options)
    {
        $this->_routes = array();
    }
   
    public function addRoute(Lily_Controller_Route_Abstract $route) {
    	$this->_routes[] = $route;
    }
    
    public function setDefaultRoute(Lily_Controller_Route_Abstract $route) {
        $this->_default = $route;
    }

    public function match($uri)
    {
    	$t = explode('?', $uri);
    	$url = isset($t[0]) ? $t[0] : '';
    	$query_string = isset($t[1]) ? $t[1] : '';
    
    	Lily_Log::write('lily', "trying to find match for $url $query_string ");

        $reversed = array_reverse($this->_routes);
        foreach($reversed as $route) {
            if ($route->match($url)) {
                return $route->getRequest($url, $query_string);
            }
        }
        return false;
    }
}

<?php

/**
 * Lilypad_Controller_Router class.
 * @author Matt Ward
 */
class Lilypad_Controller_Router 
{
    private $_routes;
    
    public function __construct($options)
    {
        $this->_routes = array();
    }
   
    public function addRoute(Lilypad_Controller_Route_Abstract $route) {
    	$this->_routes[] = $route;
    }
    
    public function setDefaultRoute(Lilypad_Controller_Route_Abstract $route) {
        $this->_default = $route;
    }

    public function match($uri)
    {
    	$t = explode('?', $uri);
    	$url = isset($t[0]) ? $t[0] : '';
    	$query_string = isset($t[1]) ? $t[1] : '';
    
    	Log::debug("trying to find match for $url $query_string ", NULL, 'LILYPAD_DEBUG');

        $reversed = array_reverse($this->_routes);
        foreach($reversed as $route) {
            if ($route->match($url)) {
                return $route->getRequest($url, $query_string);
            }
        }
        return false;
    }


}

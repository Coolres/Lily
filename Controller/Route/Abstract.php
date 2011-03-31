<?php


/**
 * Abstract Lilypad_Controller_Route_Abstract class.
 * @author Matt Ward
 * @abstract
 */
abstract class Lilypad_Controller_Route_Abstract
{
    protected $name;
    
    
    public function getName()
    {
        return $this->name;
    }
    
    abstract public function match($uri);
    
    abstract public function getRequest($uri, $query_string);
    
    protected function _parseParams(Lilypad_Controller_Request $request, $remainder)
    {
    	if (empty($remainder)) {
    		return;
    	}
    	
    	Log::debug("Parsing:", $remainder, 'LILYPAD_DEBUG');
    	
    	if (strpos('/', $remainder) > 0) {
    		$params = explode('/', $remainder);
	    	for($i=0;$i<count($params);$i+=2) {
	            if (isset($params[$i])) {
	            	if (isset($params[$i+1])) {
	            		$request->setParam(
	            			$params[$i], 
	            			stripslashes(trim(urldecode($params[$i+1]))));
	            	} else { // If no value given, assume true
	            		$request->setParam($params[$i], true);
	            	}
	            }
	        }
    	} else {
    		// Remove leading '?'
    		if (substr($remainder, 0 , 1) == '?') {
    			$remainder = substr($remainder, 1);
    		}
    		parse_str($remainder, $params);
    		foreach($params as $key => $value) {
    			if (is_array($value)) {
    				foreach ($value as $t => &$v) {
    					$v = stripslashes(trim(urldecode($v)));
    				}
    				$request->setParam($key, $value);
    			} else {
    				$request->setParam($key, stripslashes(urldecode($value)));
    			}
    		}
    		
    	} 
    }
}

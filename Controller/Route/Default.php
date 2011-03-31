<?php

/**
 * Lilypad_Controller_Route_Default class.
 * @author Matt Ward
 * @extends Lilypad_Controller_Route_Abstract
 */
class Lilypad_Controller_Route_Default extends Lilypad_Controller_Route_Abstract
{
    private $_pattern;
    private $_urlDelimiter = '/';
    
    public function __construct($name=null, $pattern=null)
    {
        if ($pattern === null) {
            $pattern = '/:controller/:action';
        }
        if ($name === null) {
            $this->name = 'default';
        }
        $this->_pattern = trim($pattern, $this->_urlDelimiter);
    }

    public function match($uri)
    {
    	Log::debug("trying to match $uri against {$this->_pattern}", NULL, 'LILYPAD_DEBUG');
    	
    	
        $uri        = trim($uri, $this->_urlDelimiter);
        $temp       = explode('?', $uri);
        $pattern    = explode('/', $this->_pattern);
        $parts      = explode('/', $temp[0]);
        
        for($i=0; $i<count($pattern); $i++) {
            $value = array_shift($parts);
            if (substr($pattern[$i], 0, 1) != ':') {
                if ($pattern[$i] != $value) {
                	return false;
                }
            }
        }
        
	    Log::debug("match found.", NULL, 'LILYPAD_DEBUG');
        return true;
    }

    public function getRequest($uri, $query_string)
    {
    	$request    = new Lilypad_Controller_Request();
        $uri        = trim($uri, $this->_urlDelimiter);
        $pattern    = explode($this->_urlDelimiter, $this->_pattern);
        $parts      = explode($this->_urlDelimiter, $uri);


        for($i=0; $i<count($pattern); $i++) {
            $value = array_shift($parts);
            if (substr($pattern[$i], 0, 1) == ':') {
                if (is_null($value) || $value == '') $value = 'index';
                $key	= strtolower(substr($pattern[$i],1));
                
                // Determine if url had imbedded data type. eg. getsomething.json, getsomething.xml
                if ($key == 'action') {
                	if (strpos($value, '.')) {
                		list($value, $param) = explode('.', $value);
                		$request->setDataType($param);
                	}
                }
                // Data type, a more direct way
                if ($key === 'data_type') {
                	$request->setDataType($param);
                } 
                
                $function_name = 'set' . ucfirst($key);
                if (is_callable(array($request,$function_name))) {
                    $request->$function_name($value);
                } else {
                	$request->setParam($key, $value);
                }
            }
        }
        
        // Turn back into query string
        $query = array();
        for($i=0; $i<count($parts); $i+=2) {
        	$key	= $parts[$i];
        	$value	= isset($parts[$i+1]) ? $parts[$i+1] : true;
        	$query[] = "{$key}={$value}";
        }
        
        $this->_parseParams($request, $query_string);
        if (!empty($query)) {
        	$this->_parseParams($request, '?' . implode('&', $query));
        }
        
        return $request;
    }
}

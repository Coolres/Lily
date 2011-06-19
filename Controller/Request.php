<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in 
 * the project root's LICENSE file. 
 */
/**
 * Lily_Controller_Request class.
 * @author Matt Ward
 */
class Lily_Controller_Request
{

    private $_module;
    private $_controller;
    private $_action;
    private $_params;
    private $_dispatched;
    private $_data_type;
    
    
    /**
     * __construct function.
     * 
     * @access public
     * @return $this
     */
    public function __construct()
    {
        $this->_dispatched = false;
    }
    
    /**
     * setModule function.
     * 
     * @access public
     * @param mixed $arg
     * @return $this
     */
    public function setModule($arg)
    {
        $this->_module = $arg;
        return $this;
    }
    
    /**
     * getModuleName function.
     * 
     * @access public
     * @return $this
     */
    public function getModuleName()
    {
        if (is_null($this->_module)) {
            return 'default';
        }
        return $this->_module;
    }

    /**
     * setController function.
     * 
     * @access public
     * @param mixed $arg
     * @return $this
     */
    public function setController($arg)
    {
        $this->_controller = $arg;
        return $this;
    }

    /**
     * getControllerName function.
     * 
     * @access public
     * @return $this
     */
    public function getControllerName()
    {
        if (is_null($this->_controller))
        {
            return 'index';
        }
        return $this->_controller;
    }
 
    /**
     * setAction function.
     * 
     * @access public
     * @param mixed $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->_action = $action;
        return $this;
    }
       
    /**
     * getActionName function.
     * 
     * @access public
     * @return string
     */
    public function getActionName()
    {
        if (is_null($this->_action)){
            return 'index';
        }
        return $this->_action;
    }
    
    /**
     * getParams function.
     * 
     * @access public
     * @return array &
     */
    public function & getParams()
    {
    	if (is_null($this->_params)) {
    		$this->_params = array();
    	}
    	return $this->_params;
    }

	/**
     * setParams function.
     * 
     * @access public
     * @param mixed array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
        return $this;
    }
    
    /**
     * getParam function.
     * 
     * @access public
     * @param mixed $param
     * @return string | NULL
     */
    public function getParam($param)
    {
        if (isset($this->_params[$param])) {
            return $this->_params[$param];
        } else if (isset($_REQUEST[$param])) {
			return $_REQUEST[$param];
        }
        return NULL;
    }

	/**
     * setParam function.
     * 
     * @access public
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function setParam($key, $value) {
        $params =& $this->getParams();
        $params[$key] = $value;
        return $this;
    }
    
    /**
     * setDispatched function.
     * 
     * @access public
     * @param mixed $arg
     * @return $this
     */
    public function setDispatched($arg)
    {
        $this->_dispatched = $arg;
        return $this;
    }
    
    /**
     * getDispatched function.
     * 
     * @access public
     * @return bool
     */
    public function getDispatched()
    {
        return $this->_dispatched;
    }
    
    /**
     * setDataType function.
     * 
     * @access public
     * @param mixed $arg
     * @return $this
     */
    public function setDataType($arg) {
    	$this->_data_type	= $arg;
    	return $this;
    }
    
    /**
     * getDataType function.
     * 
     * @access public
     * @return string
     */
    public function getDataType() {
    	return $this->_data_type;
    }

	/**
	 * getRequestMethod
	 *
	 * @author Tyler
	 */
	public function getRequestMethod() {
        if ($requestMethod = $_SERVER['REQUEST_METHOD']) {
            return $requestMethod;
        }
        return false;
    }
	
	/**
	 * isPost
	 *
	 * @author Tyler
	 */
	public function isPost() {
		if (strtolower($this->getRequestMethod()) == 'post') {
			return true;
		}
		return false;
	}

	public function getUserAgent() {
		return $_SERVER['HTTP_USER_AGENT'];
	}
	
	public function isUserAgentMobileApple() {
		$devices = array(
			'iPhone', 'iPod', 'iPad'
		);
		foreach ($devices as $device) {
			if (strpos($_SERVER['HTTP_USER_AGENT'], $device)) {
				return true;
			}
		}
		return false;
	}

	public function isUserAgentMobile() {
		$devices = array(
			'iPhone', 'iPod', 'iPad', 'BlackBerry', 'Android'
		);
		foreach ($devices as $device) {
			if (strpos($_SERVER['HTTP_USER_AGENT'], $device)) {
				return true;
			}
		}
		return false;
	}

}

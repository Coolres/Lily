<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in 
 * the project root's LICENSE file. 
 */
/**
 * Lily_Controller_Action class.
 * @author Matt Ward
 */
class Lily_Controller_Action
{
    protected $_request;
    protected $_response;

    public function __construct(Lily_Controller_Request& $request, Lily_Controller_Response& $response){
        // Instantiate smarty here.
        
        $this->_request     = $request;
        $this->_response	= $response;
    }
    
    public function preDispatch(){}
    
    public function postDispatch(){}
    
	public function assign($key, $value) {
		$this->_response->assign($key, $value);
	}
    
    public function setResponse(Lily_Controller_Response $response) {
    	$this->_response = $response;
    }
    
    public function getResponse() {
    	return $this->_response;
    }
    
    protected function setCookie($cookie_name, $value, $ttl, $dir='/', $domain=null) {
    	$this->_response->setCookie($cookie_name, $value, $ttl, $dir, $domain);
    }
    
	protected function setCookies(array $cookies) {
		foreach ($this->cookies as $cookie) {
			if ($cookie instanceof Lily_Data_Model_Cookie) {
				throw new Lily_Controller_Exception("Specified cookie is not an instance of Lily_Data_Model_Cookie");
			}
			$this->_response->setCookieObject($cookie);
		}
	}
	
    protected function getParam($param, $default=null) {
    	$t = $this->_request->getParam($param);
    	if (null === $t) {
    		return $default;
    	}
        return $t;
    }

	protected function getParams() {
		return $this->_request->getParams();
	}
    
    protected function setParam($param, $value) {
        $this->_request->setParam($param, $value);
        return $this;
    }
    
    protected function setLayout($layout_name) {
    	$this->_response->setLayout($layout_name);	
    }
    
    protected function forward($module, $controller, $action) {
        $this->_request->setDispatched(false)
            ->setModule($module)
            ->setController($controller)
            ->setAction($action);
    }
    
    protected function redirect($uri) {
        $this->_request->setDispatched(true);
        $this->_response->setNoRender();
        $this->_response->addHeader('Location: '.$uri);    
    }
    
    protected function setNoRender() {
    	$this->_response->setNoRender();
    }


}

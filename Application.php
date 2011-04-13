<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in 
 * the project root's LICENSE file. 
 */
/**
 * LilypadMVC_Application class.
 * @author Matt Ward
 */
class LilypadMVC_Application {

    private static $autoloader;
    private static $logger;
    private $_router;
    private $_dispatcher;
    private $_options;
    private $_use_user_apc = false;

    public function __construct($options=NULL)
    {  	
    	// Register autoloader.
        self::getAutoloader();
    	$this->_options = $options;
    	foreach ($this->_options as $key => $value) {
    		switch ($key) {
    			case 'use_user_apc':
    				$this->_use_user_apc = $value;
    			break;
    			
    			case 'autoloader':
    				if ($value instanceof LilypadMVC_Loader_Autoloader) {
    					self::$autoloader = $value;
    				}
    			break;
    			
    			case 'logger':
    				if ($value instanceof LilypadMVC_iLog) {
    					self::$logger = $value;
    				} elseif (is_array($value)) {
    					self::$logger = new LilypadMVC_Log($value);
    				}
    					
    			break;
    			
    			default: break;
    		}
    	}
    	
    	if (null !== self::$logger) {
    		set_error_handler(array(self::logger, 'handler'));
    	}
    }


    public function run($url=NULL)
    {
    	ob_start();
    	$dispatcher = $this->getDispatcher();
    	$response = new LilypadMVC_Controller_Response();
    	$request = null;
    	try {
	        if (is_null($url)){
	        	if (!isset($_SERVER['REQUEST_URI'])) {
	        		throw new LilypadMVC_Controller_Exception("URL not specified and SERVER Request URI not set", 500);
	        	}
	        	$url = $_SERVER['REQUEST_URI'];
	        }

			$success = false;
	        // APC optimizations
	        if ($this->_use_user_apc) {
	        	$temp = apc_fetch($url, $success);
	        }
	        if ($success) {
	        	$request = $temp;
	        } else {
		        $request = $this->getRouter()->match($url);
		        if ($request === false) {
		            throw new LilypadMVC_Controller_Exception("Could not find an appropriate route. 404", 404);
		        } else {
		        	if ($this->_use_user_apc) {
		        		apc_store($url, $request, 3600);
		        	}
		        }
	        }

	        $response = $dispatcher->dispatch($request, $response);
	        $response->render();

    	} catch (LilypadMVC_Controller_Exception $e) {
    		ob_clean();
    		$error_request = new LilypadMVC_Controller_Request();
    		$error_request->setModule('default')
    					->setController('error')
    					->setAction('error')
    					->setParam('exception', $e)
    					->setParam('code', $e->getErrorCode());
    		if (null !== $request) {
    			$error_request->setDataType($request->getDataType());
    		}
    		$response = $dispatcher->dispatch($error_request, $response);
	        $response->render();
    	} catch (Exception $e) {
    		ob_clean();
    		$error_request = new LilypadMVC_Controller_Request();
    		$error_request->setModule('default')
    					->setController('error')
    					->setAction('error')
    					->setParam('exception', $e)
						->setParam('code', '502');
    		if (null !== $request) {
    			$error_request->setDataType($request->getDataType());
    		}
    		$response = $dispatcher->dispatch($error_request, $response);
	        $response->render();
    	}
    	ob_end_flush();
    }

    public function getDispatcher()
    {
        if (is_null($this->_dispatcher)) {
            $this->_dispatcher = new LilypadMVC_Controller_Dispatcher($this->_options);
        }
        return $this->_dispatcher;
    }

    public function addRoute(LilypadMVC_Controller_Route_Abstract $route)
    {
        $router = $this->getRouter();
        $router->addRoute($route);
        return $this;
    }

    public function getRouter()
    {
        if (is_null($this->_router))
        {
        	require_once(dirname(__FILE__) . '/Controller/Router.php');
            $this->_router = new LilypadMVC_Controller_Router($this->_options);
            $route = new LilypadMVC_Controller_Route_Default();
            $this->_router->addRoute($route);
        }
        return $this->_router;
    }

    public static function getAutoloader()
    {
        if (is_null(self::$autoloader)) {
            require_once(dirname(__FILE__) . '/Loader/Autoloader.php');
            require_once(dirname(__FILE__) . '/Loader/Autoloader/Class.php');
            self::$autoloader = LilypadMVC_Loader_Autoloader::getInstance();
            $class_autoloader = new LilypadMVC_Loader_Autoloader_Class(
                array('basepath' => dirname(__FILE__), 'namespace' => 'LilypadMVC')
            );
            self::$autoloader->addAutoloader($class_autoloader);
        }
        return self::$autoloader;
    }

    public static function registerAutoloader()
    {
        self::getAutoloader();
    }
    
    public static function getLogger() {
    	if (is_null(self::$logger)) {
    		self::$logger = new LilypadMVC_Log();
    	}
    	return self::$logger;
    }
    
}
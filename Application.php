<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in
 * the project root's LICENSE file.
 */
/**
 * Lily_Application class.
 * @author Matt Ward
 */
class Lily_Application {

	private static $instance;
    private static $autoloader;
	private $_options;
    private $_router;
    private $_dispatcher;
	
	private $_use_apc;
	private $_controller_dir;
	private $_template_dir;
	private $_partial_dir;

	private $_registry;

    public function __construct(Lily_Config_Ini $ini)
    {
    	self::getAutoloader();
		self::$instance = $this;
		
		$this->_registry = Lily_Registry::getInstance();
		
		// I can either iterate through all ini, 
		// or pick out the ones this system will care about
		foreach ($ini->get() as $module => $payload) {
			switch ($module) {
				case 'constants':
				case 'constant' :
					foreach ($payload as $key => $value) {
						if (!defined($key)) define($key, $value);
					}
					break;
					
				case 'database' :
					$manager = new Lily_Database_Manager($payload);
					break;
					
				case 'facebook' :
					$manager = new Lily_Facebook_Manager($payload);
					break;
					 
				case 'jsonrpc' :
					$manager = new Lily_Jsonrpc_Manager($payload);
					break;
				
				case 'lily':
				case 'lilypad':
					$this->init($payload);
					break;
					
				case 'log' :
					$logger = new Lily_Log($payload);
					break;
					
				case 'memcached' :
					$manager = new Lily_Memcached_Manager($payload);
					break;
					
				case 'php' :
					foreach ($payload as $key => $value) {
						ini_set($key, $value);
					}
					break;

				case 'recaptcha' :
					$manager = new Lily_Recaptcha_Manager($payload);
					break;
					
				case 'thrift' :
					$manager = new Lily_Thrift_Manager($payload);
					break;
					
				case 'twitter' :
					$manager = new Lily_Twitter_Manager($payload);
					break;
				
				case 'xmlrpc' :
					$manager = new Lily_Xmlrpc_Manager($payload);
					break;
					
				default: break;
			}
		}
    }

	private function init($options) {
		$this->_use_apc = isset($options['apc']) ? $options['apc'] : false;
		if (isset($options['dispatcher'])) {
			$this->_dispatcher = new Lily_Controller_Dispatcher($options['dispatcher']);
		}


		if (isset($options['lib'])) {
			$other_autoloader = new Lily_Loader_Autoloader_Class(
                array('basepath' => $options['lib'], 'namespace' => '')
            );
            self::$autoloader->addAutoloader($other_autoloader);
		}
	}

    public function run($url=NULL)
    {
    	ob_start();
    	$dispatcher = $this->getDispatcher();
    	$response = new Lily_Controller_Response();
    	$request = null;
    	try {
	        if (is_null($url)){
	        	if (!isset($_SERVER['REQUEST_URI'])) {
	        		throw new Lily_Controller_Exception("URL not specified and SERVER Request URI not set", 500);
	        	}
	        	$url = $_SERVER['REQUEST_URI'];
	        }

			$success = false;
	        // APC optimizations
	        if ($this->_use_apc) {
	        	$temp = apc_fetch($url, $success);
	        }
	        if ($success) {
	        	$request = $temp;
	        } else {
		        $request = $this->getRouter()->match($url);
		        if ($request === false) {
		            throw new Lily_Controller_Exception("Could not find an appropriate route. 404", 404);
		        } else {
		        	if ($this->_use_apc) {
		        		apc_store($url, $request, 3600);
		        	}
		        }
	        }

	        $response = $dispatcher->dispatch($request, $response);
	        $response->render();

    	} catch (Lily_Controller_Exception $e) {
    		ob_clean();
    		$error_request = new Lily_Controller_Request();
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
    		$error_request = new Lily_Controller_Request();
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
            $this->_dispatcher = new Lily_Controller_Dispatcher($this->_options);
        }
        return $this->_dispatcher;
    }

    public function addRoute(Lily_Controller_Route_Abstract $route)
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
            $this->_router = new Lily_Controller_Router($this->_options);
            $route = new Lily_Controller_Route_Default();
            $this->_router->addRoute($route);
        }
        return $this->_router;
    }

    public static function getAutoloader()
    {
        if (is_null(self::$autoloader)) {
            require_once(dirname(__FILE__) . '/Loader/Autoloader.php');
            require_once(dirname(__FILE__) . '/Loader/Autoloader/Class.php');
            self::$autoloader = Lily_Loader_Autoloader::getInstance();
            $class_autoloader = new Lily_Loader_Autoloader_Class(
                array('basepath' => dirname(__FILE__), 'namespace' => 'Lily')
            );
            self::$autoloader->addAutoloader($class_autoloader);
        }
        return self::$autoloader;
    }

    public static function registerAutoloader()
    {
        self::getAutoloader();
    }
}
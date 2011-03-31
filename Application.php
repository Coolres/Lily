<?php

/**
 * Lilypad_Application class.
 * @author Matt Ward
 */
class Lilypad_Application {

    private static $autoloader;
    private $_router;
    private $_dispatcher;

    public function __construct($options=NULL)
    {
        // Register autoloader.
        self::getAutoloader();
        $this->_options = $options;
    }


    public function run($url=NULL)
    {
    	ob_start();
    	$dispatcher = $this->getDispatcher();
    	$request = null;
    	try {
	        if (is_null($url)){
	        	if (!isset($_SERVER['REQUEST_URI'])) {
	        		throw new Lilypad_Controller_Exception("URL not specified and SERVER Request URI not set", 500);
	        	}
	        	$url = $_SERVER['REQUEST_URI'];
	        }

			$success = false;
	        // APC optimizations
	        if (defined('USE_APC_USER') && constant('USE_APC_USER')) {
	        	$temp = apc_fetch($url, $success);
	        }
	        if ($success) {
	        	$request = $temp;
	        } else {
		        $request = $this->getRouter()->match($url);
		        if ($request === false) {
		            throw new Lilypad_Controller_Exception("Could not find an appropriate route. 404", 404);
		        } else {
		        	if (defined('USE_APC_USER') && constant('USE_APC_USER')) {
		        		apc_store($url, $request, 3600);
		        	}
		        }
	        }

	        $response = $dispatcher->dispatch($request);
	        $response->render();

    	} catch (Lilypad_Controller_Exception $e) {
    		ob_clean();
    		$error_request = new Lilypad_Controller_Request();
    		$error_request->setModule('default')
    					->setController('error')
    					->setAction('error')
    					->setParam('exception', $e)
    					->setParam('code', $e->getErrorCode());
    		if (null !== $request) {
    			$error_request->setDataType($request->getDataType());
    		}
    		$response = $dispatcher->dispatch($error_request);
	        $response->render();
    	} catch (Exception $e) {
    		ob_clean();
    		$error_request = new Lilypad_Controller_Request();
    		$error_request->setModule('default')
    					->setController('error')
    					->setAction('error')
    					->setParam('exception', $e)
						->setParam('code', '502');
    		if (null !== $request) {
    			$error_request->setDataType($request->getDataType());
    		}
    		$response = $dispatcher->dispatch($error_request);
	        $response->render();
    	}
    	ob_end_flush();
    }

    public function getDispatcher()
    {
        if (is_null($this->_dispatcher)) {
            $this->_dispatcher = new Lilypad_Controller_Dispatcher($this->_options);
        }
        return $this->_dispatcher;
    }

    public function addRoute(Lilypad_Controller_Route_Abstract $route)
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
            $this->_router = new Lilypad_Controller_Router($this->_options);
            $route = new Lilypad_Controller_Route_Default();
            $this->_router->addRoute($route);
        }
        return $this->_router;
    }

    public static function getAutoloader()
    {
        if (is_null(self::$autoloader)) {
            require_once(dirname(__FILE__) . '/Loader/Autoloader.php');
            require_once(dirname(__FILE__) . '/Loader/Autoloader/Class.php');
            self::$autoloader = Lilypad_Loader_Autoloader::getInstance();
            $class_autoloader = new Lilypad_Loader_Autoloader_Class(
                array('basepath' => dirname(__FILE__), 'namespace' => 'Lilypad')
            );
            self::$autoloader->addAutoloader($class_autoloader);

            $other_autoloader = new Lilypad_Loader_Autoloader_Class(
                array('basepath' => dirname(dirname(__FILE__)), 'namespace' => '')
            );
            self::$autoloader->addAutoloader($other_autoloader);
        }
        return self::$autoloader;
    }

    public static function registerAutoloader()
    {
        self::getAutoloader();
    }
}

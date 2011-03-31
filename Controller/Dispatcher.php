<?php

/**
 * Lilypad_Controller_Dispatcher class.
 * @author Matt Ward
 */
class Lilypad_Controller_Dispatcher
{
    protected $_dirs;
    protected $_view_class;
    
    public function __construct($options)
    {
    	if (is_null($options)) {
    		throw new Lilypad_Controller_Exception(
    			"Configuration options not set. Need controller_dir, template_dir, and partial_dir set for at least one module");
    	}
    	
    	if (is_array($options) && isset($options['dirs'])) {
    		if (!isset($options['dirs'])) {
    			throw new Lilypad_Controller_Exception(
    				"Configuration options not set. Need controller_dir, template_dir, and partial_dir set for at least one module");
    		}
    		
    		if (!isset($options['view_class'])) {
    			throw new Lilypad_Controller_Exception(
    				"Default view class not specified");
    		}
    		$this->_view_class	 = $options['view_class'];
    		
    		foreach ($options['dirs'] as $name => $module) {
    			if (!isset($module['controller_dir'])) {
    				throw new Lilypad_Controller_Exception("'controller_dir not set for module '$name'");
    			}
    			
    			if (!isset($module['template_dir'])) {
    				throw new Lilypad_Controller_Exception("'template_dir not set for module '$name'");
    			}
    			
    			if (!isset($module['partial_dir'])) {
    				throw new Lilypad_Controller_Exception("'partial_dir not set for module '$name'");
    			}
    		}
    		$this->_dirs = $options['dirs'];
    	}
    }
    
    public function formatControllerName($string)
    {
        return ucfirst($string) . 'Controller';
    }
    
    public function formatActionName($string)
    {
        return Utility::toCamelCase($string, false) . 'Action';
    }
    
    public function dispatch(Lilypad_Controller_Request $request)
    {
        if (is_null($request) || $request === false) {
            throw new Lilypad_Controller_Exception('Page could not be found', 404);
        }
        
        if (!isset($this->_dirs[$request->getModuleName()])) {
            throw new Lilypad_Controller_Exception("No directories specified for '{$request->getModule()}' module.", 500);   
        }
            
        $response = new Lilypad_Controller_Response();
        
        for ($i=0; $i < 4; $i++) {
        	if ($request->getDispatched()) {
        		break;
        	}
        	
        	$controller_dir		= $this->_dirs[$request->getModuleName()]['controller_dir'];
        	$template_dir		= $this->_dirs[$request->getModuleName()]['template_dir'];
        	$response->setTemplateDir($template_dir);
        	$partial_dir		= $this->_dirs[$request->getModuleName()]['partial_dir'];
        	
        	$controller_class	= $this->formatControllerName($request->getControllerName());
        	$controller_path	= $controller_dir . '/' . $controller_class . '.php';
        							
        	if (is_file($controller_path)) {
        		// Get the appropriate controller
        		require_once($controller_path);
        		$controller		= new $controller_class($request, $response);
        		$action			= $this->formatActionName($request->getActionName());
        	Log::debug(" Will invoke {$controller_class}->{$action}()", NULL, 'LILYPAD_DEBUG');
        		
        		$class_methods	= get_class_methods($controller);
        		if (!in_array($action, $class_methods)) {
        			throw new Lilypad_Controller_Exception("Specified action, '$action' could not be found", 404);
        		}
        		
        		// Execute the requested action
        		$controller->preDispatch();                
                $data = $controller->$action();
                if (null !== $data) {
                	$response->assign('data', $data);
                }
                $controller->postDispatch();
                
        			
        		$data_type		= $request->getDataType();
        		switch ($data_type) {
        			case 'json':
        				$view	= new Lilypad_View_Abstract($partial_dir);
        				$response->setView($view);
        				$response->setTemplate('json');
        				$response->addHeader('Content-Type: application/json; charset=utf-8');
						break;
						
					case 'jsonp':
						$view	= new Lilypad_View_Abstract($partial_dir);
        				$response->setView($view);
        				$response->setTemplate('jsonp');
        				$response->addHeader('Content-Type: text/html');
						break;
			
					case 'css':
						$response->addHeader('Content-Type: text/css');
						
					default:
						$template_path = ucfirst($request->getControllerName())
            				. '/' . strtolower($request->getActionName());	
            			$view	= new $this->_view_class($partial_dir);
        				if (null === $response->getTemplate()) {
							$response->setTemplate($template_path);
						}
            			$response->setView($view);
						break;
        		}
				$request->setDispatched(true);
        	}
        	
        }
        
        if ($request->getDispatched() == false) {
        	throw new Lilypad_Controller_Exception("Could not find {$request->getControllerName()}/{$request->getActionName()}", 404 );
        }
        
        return $response;
    }



}

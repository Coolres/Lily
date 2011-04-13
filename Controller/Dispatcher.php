<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in 
 * the project root's LICENSE file. 
 */
/**
 * Lilypad_Controller_Dispatcher class.
 * @author Matt Ward
 */
class LilypadMVC_Controller_Dispatcher
{
    protected $_modules;
    
    public function __construct($options)
    {
    	if (is_null($options)) {
    		throw new LilypadMVC_Controller_Exception(
    			"Configuration options not set. Need controller_dir, template_dir, and partial_dir set for at least one module");
    	}
    	
    	if (is_array($options) && isset($options['modules'])) {
    		if (!isset($options['modules'])) {
    			throw new LilypadMVC_Controller_Exception(
    				"Configuration options not set. Need controller_dir, template_dir, and partial_dir set for at least one module");
    		}

    		foreach ($options['modules'] as $name => $module) {
    			if (!isset($module['controller_dir'])) {
    				throw new LilypadMVC_Controller_Exception("'controller_dir not set for module '$name'");
    			}
    			
    			if (!isset($module['template_dir'])) {
    				throw new LilypadMVC_Controller_Exception("'template_dir not set for module '$name'");
    			}
    			
    			if (!isset($module['partial_dir'])) {
    				throw new LilypadMVC_Controller_Exception("'partial_dir not set for module '$name'");
    			}
    			
    			if (!isset($module['layout_dir'])) {
    				throw new LilypadMVC_Controller_Exception("'layout_dir' not set for module '$name'");
    			}
    			
    			if (!isset($module['view_class'])) {
    				$options['modules'][$name]['view_class'] = 'LilypadMVC_View_Abstract';
    			}
    		}
    		$this->_modules = $options['modules'];
    	}
    }
    
    public function formatControllerName($string)
    {
        return ucfirst($string) . 'Controller';
    }
    
    public function formatActionName($string)
    {
        return LilypadMVC_Utility::toCamelCase($string, false) . 'Action';
    }
    
    public function dispatch(LilypadMVC_Controller_Request $request, LilypadMVC_Controller_Response& $response)
    {
        if (is_null($request) || $request === false) {
            throw new LilypadMVC_Controller_Exception('Page could not be found', 404);
        }
        $module = $request->getModuleName();
        if (!isset($this->_modules[$module])) {
            throw new LilypadMVC_Controller_Exception("No directories specified for '{$module}' module.", 500);   
        }
        
        if (null === $response) {
        	$response = new LilypadMVC_Controller_Response();
        }
        $log = LilypadMVC_Application::getLogger();
        
        for ($i=0; $i < 4; $i++) {
        	if ($request->getDispatched()) {
        		break;
        	}
        	
        	$controller_dir		= $this->_modules[$module]['controller_dir'];
        	$template_dir		= $this->_modules[$module]['template_dir'];
        	$layout_dir			= $this->_modules[$module]['layout_dir'];
        	$partial_dir		= $this->_modules[$module]['partial_dir'];
        	$view_class			= $this->_modules[$module]['view_class'];
        	$response->setTemplateDir($template_dir);
        	$response->setLayoutDir($layout_dir);
        	
        	$controller_class	= $this->formatControllerName($request->getControllerName());
        	$controller_path	= $controller_dir . '/' . $controller_class . '.php';
        							
        	if (is_file($controller_path)) {
        		// Get the appropriate controller
        		require_once($controller_path);
        		$controller		= new $controller_class($request, $response);
        		$action			= $this->formatActionName($request->getActionName());
        		$log->debug(" Will invoke {$controller_class}->{$action}()", NULL, 'LILYPAD_DEBUG');
        		
        		$class_methods	= get_class_methods($controller);
        		if (!in_array($action, $class_methods)) {
        			throw new LilypadMVC_Controller_Exception("Specified action, '$action' could not be found", 404);
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
        				$view	= new LilypadMVC_View_Abstract($partial_dir);
        				$response->setView($view);
        				$response->setTemplate('json');
        				$response->setLayout(null);
        				$response->addHeader('Content-Type: application/json; charset=utf-8');
						break;
						
					case 'jsonp':
						$view	= new LilypadMVC_View_Abstract($partial_dir);
        				$response->setView($view);
        				$response->setTemplate('jsonp');
        				$response->setLayout(null);
        				$response->addHeader('Content-Type: text/html');
						break;
			
					case 'css':
						$response->addHeader('Content-Type: text/css');
						
					default:
            			$view	= new $view_class($partial_dir);
        				if (null === $response->getTemplate()) {
        					$template_path = ucfirst($request->getControllerName())
            				. '/' . strtolower($request->getActionName());
							$response->setTemplate($template_path);
						}
						
						if (null === $response->getLayout()) {
							$response->setLayout('Main');
						}
            			$response->setView($view);
						break;
        		}
				$request->setDispatched(true);
        	}
        	
        }
        
        if ($request->getDispatched() == false) {
        	throw new LilypadMVC_Controller_Exception("Could not find {$request->getControllerName()}/{$request->getActionName()}", 404 );
        }
        
        return $response;
    }
}
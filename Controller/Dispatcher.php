<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in
 * the project root's LICENSE file.
 */
/**
 * lily_Controller_Dispatcher class.
 * @author Matt Ward
 */
class Lily_Controller_Dispatcher
{
	protected $_view_class;
    protected $_modules;

    public function __construct($options)
    {
		if (is_null($options)) {
    		throw new Lily_Config_Exception('lily.dispatcher.modules');
    	}
    	
    	if (is_array($options)) {
    		if (isset($options['modules'])) {
    			$this->_modules = $options['modules'];
    		}
    	}
    }

    public function formatControllerName($string)
    {
        return ucfirst($string) . 'Controller';
    }

    public function formatActionName($string)
    {
        return Lily_Utility::toCamelCase($string, false) . 'Action';
    }

    public function dispatch(Lily_Controller_Request $request, Lily_Controller_Response& $response)
    {
        if (is_null($request) || $request === false) {
            throw new Lily_Controller_Exception('Page could not be found', 404);
        }

		if (null === $response) {
        	$response = new Lily_Controller_Response();
        }
		$response->assign('mobile', $request->isUserAgentMobile());
        $response->assign('mobile_apple', $request->isUserAgentMobileApple());
		 
        // $module = $request->getModuleName();
        // if (!isset($this->_modules[$module])) {
            // throw new Lily_Controller_Exception("No directories specified for '{$module}' module.", 500);
        // }
        for ($i=0; $i < 4; $i++) {
        	if ($request->getDispatched()) {
        		break;
        	}

        	$controller_dir		= $this->getControllerDir($request->getModuleName());//$this->_dirs[$request->getModuleName()]['controller_dir'];
        	$template_dir		= $this->getTemplateDir($request->getModuleName());//$this->_dirs[$request->getModuleName()]['template_dir'];
        	
        	$controller_class	= $this->formatControllerName($request->getControllerName());
        	$controller_path	= $controller_dir . '/' . $controller_class . '.php';
			
        	//$layout_dir			= $this->_modules[$module]['layout_dir'];
        	// Whats it for
        	// $view_class			= $this->_modules[$module]['view_class'];
        	// $response->setTemplateDir($template_dir);
        	// $response->setLayoutDir($layout_dir);

        	if (is_file($controller_path)) {
        		// Get the appropriate controller
        		require_once($controller_path);
        		$controller		= new $controller_class($request, $response);
        		$action			= $this->formatActionName($request->getActionName());
        		Lily_Log::write('lily', "Will invoke {$controller_class}->{$action}()");

        		$class_methods	= get_class_methods($controller);
        		if (!in_array($action, $class_methods)) {
        			throw new Lily_Controller_Exception("Specified action, '$action' could not be found", 404);
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
        				$view	= new Lily_View_Json();
        				$response->setView($view);
        				$response->setTemplate('json');
        				$response->addHeader('Content-Type: application/json; charset=utf-8');
						break;

					case 'jsonp':
						$view	= new Lily_View_Abstract($partial_dir);
        				$response->setView($view);
        				$response->setTemplate('jsonp');
        				$response->addHeader('Content-Type: text/html');
						break;

					case 'css':
						$response->addHeader('Content-Type: text/css');

					default:
						$view = $this->getView($request->getModuleName());

						$template_path = $this->getTemplateDir();
        				if (null === $response->getTemplate()) {
        					$template_path .= '/' . ucfirst($request->getControllerName())
            				. '/' . strtolower($request->getActionName());
						} else {
							$template_path .= $response->getTemplate();
						}
						$response->setTemplate($template_path);
						
						$layout_path  = $this->getLayoutDir();
						if (null === $response->getLayout()) {
							$layout_path .= '/Main';
						} else {
							$layout_path .= '/' . $response->getLayout();
						}
						$response->setLayout($layout_path);
            			$response->setView($view);
						break;
        		}
				$request->setDispatched(true);
        	}

        }

        if ($request->getDispatched() == false) {
        	throw new Lily_Controller_Exception("Could not find {$request->getControllerName()}/{$request->getActionName()}", 404 );
        }

        return $response;
    }

	public function getControllerDir($module='default') {
		if (!isset($this->_modules[$module])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}");
		}
		if (!isset($this->_modules[$module]['controllers'])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}.controllers");
		}
		return $this->_modules[$module]['controllers'];
	}

	public function getTemplateDir($module='default') {
		if (!isset($this->_modules[$module])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}");
		}	
		if (!isset($this->_modules[$module]['templates'])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}.templates");
		}
		return $this->_modules[$module]['templates'];
	}
	
	public function getLayoutDir($module='default') {
		if (!isset($this->_modules[$module])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}");
		}	
		if (!isset($this->_modules[$module]['layouts'])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}.layouts");
		}
		return $this->_modules[$module]['layouts'];
	}
	
	public function getPartialDir($module='default') {
		if (!isset($this->_modules[$module])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}");
		}
		if (!isset($this->_modules[$module]['partials'])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}.partials");
		}
		return $this->_modules[$module]['partials'];
	}
	
	public function getView($module='default') {
		if (!isset($this->_modules[$module])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}");
		}
		if (!isset($this->_modules[$module]['view'])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}.view");
		}
		if (!isset($this->_modules[$module]['view']['class'])) {
			throw new Lily_Config_Exception("lily.dispatcher.module.{$module}.view.class");
		}
		$class = $this->_modules[$module]['view']['class'];
		$view = new $class($this->_modules[$module]['view']);
		return $view;
	}
}
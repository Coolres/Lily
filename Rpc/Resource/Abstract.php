<?php

abstract class Lily_Rpc_Resource_Abstract
{
	protected $_meta;
	
	/**
	 * Expects an array of options like the following
	 *
	 * 		$options = array(
	 * 		'name'=> 'yfrog_TopData',
	 * 		'path'	=> 'something.php'
	 * 		'default_role'=> 'slave',
	 * 		'methods'=> array(
	 * 			'top_dataapi_v1_save_data'	=> array(
	 * 				'role'	=> 'master'
	 * 			)
	 * 		));
	 */
	public function __construct($config) {
		$this->_meta = $config;
	}
	
	public function getName() {
		if (!isset($this->_meta)) {
			throw new Lily_Config_Exception('xmlrpc.resource.$name.name');
		}
		return $this->_meta['name'];
	}
	
	public function getPath() {
		if (isset($this->_meta['path'])) {
			return $this->_meta['path'];
		}
		return '/';
	}
	
	public function getDefaultRole() {
		if (isset($this->_meta['default_role'])) {
			return $this->_meta['default_role'];
		}
		return null;
	}
	
	public function getMethodMeta($method) {
		if (!isset($this->_meta['methods'])) {
			throw new Lily_Config_Exception('xmlrpc.resource.$name.methods');
		}
		
		$result = array();
		if (isset($this->_meta['methods'][$method])) {
			$result = $this->_meta['methods'][$method];
		}
		
		if (!isset($result['role'])) {
			$default = $this->getDefaultRole();
			if (null === $default) {
				throw new Lily_Config_Exception('xmlrpc.resource.$name.methods.' . $method . ' or xmlrpc.resource.$name.default_role');
			}
			$result['role'] = $default;
		}

		if (!isset($result['method'])) {
			$result['method'] = $method;
		}
		
		if (!isset($result['path'])) {
			$result['path'] = $this->getPath();
		}

		return $result;
	}
}

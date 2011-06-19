<?php


class Lily_Xmlrpc_Manager
{
	private static $instance;
	private $connections = array();
	private $roles = array();
	private $adapters = array();
	private $clients = array();
	
	public function __construct($options=null) 
	{
		if (isset($options['role'])) {
			$this->roles = $options['role'];	
		}
		if (isset($options['adapter'])) {
			$this->adapters = $options['adapter'];
		}
		self::$instance = $this;
	}
	
	public function __toString() {
		return __CLASS__;
	}
	
	public function getAdapter($role = 'default') {
		if ( !isset($this->roles[$role]) ) {
			throw new Lily_Config_Exception("xmlrpc.role.$role");
		}
		
		if ( !isset($this->connections[$role]) ) {
			$options = $this->roles[$role];
			if ( !isset($options['adapter']) ) {
				throw new Lily_Config_Exception("xmlrpc.role.$role.adapter");
			}
			$adapter_type = $options['adapter'];
			if ( isset($this->adapters[$adapter_type]) ) {
				$options = array_replace_recursive(
					$this->adapters[$adapter_type],
					$options
					);
			}
			$class = 'Lily_Xmlrpc_Adapter_' . ucfirst($adapter_type);
			$adapter = new $class($options);
			$this->connections[$role] = $adapter;
		}
		return $this->connections[$role];
	}
	
	public static function getClient($client_name) {
		if (null === self::$instance) {
			throw new Exception($this . " not initialized");
		}		
	}
}
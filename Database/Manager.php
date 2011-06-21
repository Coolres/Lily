<?php


class Lily_Database_Manager
{
	private static $instance;
	private $connections = null;
	private $role_config = null;
	
	public function __construct($options) 
	{
		if (null !== self::$instance) {
			throw new Exception ("Lily_Database_Manager already instantiated");
		}
		if (isset($options['role'])) {
			$this->role_config = $options['role'];	
		}
		self::$instance = $this;
	}
	
	public function __toString() {
		return __CLASS__;
	}
	
	public static function getAdapter($role = 'default') {
		if (null === self::$instance) {
			throw new Exception(__CLASS__ . " manager not initialized");
		}
		
		if ( !isset(self::$instance->connections[$role]) ) {
			
			if ( !isset(self::$instance->role_config[$role]) ) {
				throw new Lily_Config_Exception("database.role.$role");
			}
			
			$options = self::$instance->role_config[$role];
			if ( !isset($options['adapter']) ) {
				throw new Lily_Config_Exception("database.role.$role.adapter");
			}
			
			$adapter = null;
			$class = 'Lily_Database_Adapter_' . $options['adapter'];
			if (class_exists($class)) {
				$adapter = new $class($options);
			} else {
				$class = $options['adapter'];
				if (class_exists($class)) {
					$adapter = new $class($options);
				}
			}
			
			if (null === $adapter) {
				throw new Exception("Could not find specified class for specified adapter, {$options['adapter']}");
			}
			
			self::$instance->connections[$role] = $adapter;
		}
		return self::$instance->connections[$role];
	}
}
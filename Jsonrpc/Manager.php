<?php


class Lily_Jsonrpc_Manager
{
	private static $instance;
	public static $request_id = 0;
	
	private $role_config;
	private $resource_config;
	private $adapter_config;
	
	private $resources = array();
	private $adapters = array();
	private $clients = array();
	
	
	public function __construct($options=null) 
	{
		if (isset($options['role'])) {
			$this->role_config = $options['role'];	
		}
		if (isset($options['adapter'])) {
			$this->adapter_config = $options['adapter'];
		}
		if (isset($options['resource'])) {
			$this->resource_config = $options['resource'];
		}
		
		self::$instance = $this;
	}
	
	public function __toString() {
		return __CLASS__;
	}
	
	public static function getAdapter($role = 'default') {
		if (null === self::$instance) {
			throw new Exception(__CLASS__ . " not initialized");
		}
		if ( !isset(self::$instance->role_config[$role]) ) {
			throw new Lily_Config_Exception("jsonrpc.role.$role");
		}
		
		if ( !isset(self::$instance->adapters[$role]) ) {
			$options = self::$instance->role_config[$role];
			if ( !isset($options['adapter']) ) {
				throw new Lily_Config_Exception("jsonrpc.role.$role.adapter");
			}
			$adapter_type = $options['adapter'];
			$adapter_options = isset(self::$instance->adapter_config[$adapter_type]) ?
				self::$instance->adapter_config[$adapter_type] : array();
			$class = 'Lily_Jsonrpc_Adapter_' . ucfirst($adapter_type);
			$adapter = new $class($options);
			$adapter->setOptions($options);
			self::$instance->adapters[$role] = $adapter;
		}
		return self::$instance->adapters[$role];
	}
	
	public static function getClient($client_name) {
		if (null === self::$instance) {
			throw new Exception(__CLASS__ . " manager not initialized");
		}
		if (!isset(self::$instance->clients[$client_name])) {
			$resource = self::getResource($client_name);
			self::$instance->clients[$client_name] = new Lily_Jsonrpc_Client($resource);
		}
		return self::$instance->clients[$client_name];
	}
	
	public static function getResource($resource_name) {
		if (null === self::$instance) {
			throw new Exception(__CLASS__ . " manager not initialized");
		}
		if (!isset(self::$instance->resources[$resource_name])) {
			if (class_exists($resource_name)) {
				self::$instance->resources[$resource_name] = new $resource_name();
			} else {
				if (null === self::$instace->resouce_config) {
					throw new Lily_Config_Exception("jsonrpc.resource");
				}
				if (!isset(self::$instance->resource_config[$resource_name])) {
					throw new Lily_Config_Exception("jsonrpc.resource.$client_name or class by name of $client_name");
				}
				self::$instance->resources[$resource_name] = new Lily_Jsonrpc_Resource(self::$instance->resource_config[$resource_name]);
			}	
		}
		return self::$instance->resources[$resource_name];
	}
	
	public static function getServer($role) {
		if (null === self::$instance) {
			throw new Exception(__CLASS__ . " manager not initialized");
		}
		$adapter = self::getAdapter($role);
		$server = new Lily_Jsonrpc_Server($adapter);
		return $server;
	}
}
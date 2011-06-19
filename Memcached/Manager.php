<?php


class Lily_Memcached_Manager
{
	private static $instance;
	private $wrappers;
	private $roles;
	
	public function __construct($options=array()) {
		if (null !== self::$instance) {
			throw new Exception("Cannot declare more instances of Memcached_Manager");
		}
		if (isset($options)) {
			if (isset($options['role'])) {
				$this->roles = $options['role'];
			}
		}
		self::$instance = $this;
	}
	
	public static function get($role='default') {
		if (null === self::$instance) {
			throw new Exception('Memcached_Manager not initialized.');
		}
		if ( !isset(self::$instance->roles[$role]) ) {
			throw new Lily_Config_Exception("memcached.role.{$role}");
		}
		
		if ( !isset(self::$instance->wrappers[$role]) ) {
			self::$instance->wrappers[$role] = new Lily_Memcached_Adapter(
				self::$instance->roles[$role]);
		}
		return self::$instance->wrappers[$role];
	}
}

<?php

class Lily_Registry {
		
	private static $instance;
	private $values;
	
	
	private function __construct() {}
	
	public static function getInstance() {
		if (self::$instance === null) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}
	
	
	public static function set($key, $value, $overwrite = false) {
		$instance = self::getInstance();
		
		if ( !isset($instance->values[$key]) || $overwrite) {
			$instance->values[$key] = $value;
		} else {
			return false;	
		}
		return true;
	}
	
	
	public static function get($key, $value) {
		$instance = self::getInstance();
		if (isset($instance->values[$key])) {
			return $instance->values[$key];
		}
		return null;
	}
}

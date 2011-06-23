<?php

class Lily_Facebook_Manager
{
	private static $instance;
	
	private $appid;
	private $secret;
	
	
	public function __construct(array $options) {
		if (null !== self::$instance) {
			throw new Exception(__CLASS__ . " previously initialized.");
		}
		
		if ( !isset($options['lib']) ) {
			throw new Lily_Config_Exception('facebook.lib');
		}
		if ( !file_exists($options['lib']) ) {
			throw new Exception("Could not include facebook library, {$options['lib']}");
		}
		require_once($options['lib']);
		
		if (!isset($options['appid'])) {
			throw new Lily_Config_Exception("facebook.appid");
		}
		$this->appid = $options['appid'];
		
		if (!isset($options['secret'])) {
			throw new Lily_Config_Exception("facebook.secret");
		}
		$this->secret = $options['secret'];
		self::$instance = $this;
	}
	
	public static function getClient($options=array()) {
		if (null === self::$instance) {
			throw new Exception(__CLASS__ . " not initialized.");
		}
		
		if (!isset($options['appId'])) {
			$options['appId'] = self::$instance->appid;
		}
		
		if (!isset($options['secret'])) {
			$options['secret'] = self::$instance->secret;
		}
		
		$facebook = new Facebook($options);
		$wrapper = new Lily_Facebook_Wrapper($facebook);
		return $wrapper;
	}
	
	public static function getAppId() {
		return self::$instance->appid;
	}
	
	public static function getSecret() {
		return self::$instance->appid;
	}
	
}

<?php

class Lily_LinkedIn_Manager
{
	private static $instance;
	
	private $appkey;
	private $appsecret;
	
	
	public function __construct(array $options) {
		if (null !== self::$instance) {
			throw new Exception(__CLASS__ . " previously initialized.");
		}
		
		if ( !isset($options['lib']) ) {
			throw new Lily_Config_Exception('linkedin.lib');
		}
		if ( !file_exists($options['lib']) ) {
			throw new Exception("Could not include linkedin library, {$options['lib']}");
		}
		require_once($options['lib']);
		
		if (!isset($options['appkey'])) {
			throw new Lily_Config_Exception("linkedin.appkey");
		}
		$this->apikey = $options['appkey'];
		
		if (!isset($options['appsecret'])) {
			throw new Lily_Config_Exception("linkedin.appsecret");
		}
		$this->appsecret = $options['appsecret'];
		self::$instance = $this;
	}
	
	public static function getClient($options=array()) {
		if (null === self::$instance) {
			throw new Exception(__CLASS__ . " not initialized.");
		}
		
		if (!isset($options['appKey'])) {
			$options['appKey'] = self::$instance->appkey;
		}
		
		if (!isset($options['appSecret'])) {
			$options['appSecret'] = self::$instance->appsecret;
		}
		
		$linkedin = new LinkedIn($options);
		$wrapper = new Lily_LinkedIn_Wrapper($linkedin);
		return $wrapper;
	}
	
}

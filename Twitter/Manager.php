<?php

class Lily_Twitter_Manager
{
	private static $instance;
	
	private $public_key;
	private $private_key;
	
	
	public function __construct(array $options) {
		if (null !== self::$instance) {
			throw new Exception(__CLASS__ . " previously initialized.");
		}
		
		if ( !isset($options['lib']) ) {
			throw new Lily_Config_Exception('twitter.lib');
		}
		if ( !file_exists($options['lib']) ) {
			throw new Exception("Could not include twitter library, {$options['lib']}");
		}
		require_once($options['lib']);
		
		if (isset($options['oauth'])) {
			if (!isset($options['oauth']['public'])) {
				throw new Lily_Config_Exception("twitter.oauth.public");
			}
			$this->public_key = $options['oauth']['public'];
			
			if (!isset($options['oauth']['private'])) {
				throw new Lily_Config_Exception("twitter.oauth.private");
			}
			$this->private_key = $options['oauth']['private'];
		}
		
		self::$instance = $this;
	}
	
	public static function getClient($token=null, $access_token=null) {
		if (null === self::$instance) {
			throw new Exception(__CLASS__ . " not initialized.");
		}
		
		$client = new TwitterOauth(
			self::$instance->public_key, 
			self::$instance->private_key, 
			$token, 
			$access_token
		);
		$wrapper = new Lily_Twitter_Wrapper($client);
		return $wrapper;
	}
	
}

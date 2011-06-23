<?php

class Lily_Facebook_Wrapper
{
	private $oauth_client;
	
	public function __construct(Facebook $facebook) {
		$this->oauth_client = $facebook;
	}
	
	public function __call($method, $arguments) {
		// For debugging
		Lily_Log::write('facebook', $method, $arguments);
		$result = call_user_func_array(array($this->oauth_client, $method), $arguments);
		return $result;
	}
	
	public function __get($arg) {
		return $this->oauth_client->$arg;
	}
	
	public function __set($arg, $val) {
		$this->oauth_client->$arg = $val;
	}
}

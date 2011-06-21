<?php

class Lily_Twitter_Wrapper
{
	private $oauth_client;
	
	
	public function __construct(TwitterOauth $client) {
		$this->oauth_client = $client;
	}
	
	public function __call($method, $arguments) {
		// For debugging
		Lily_Log::write('twitter', $method, $arguments);
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

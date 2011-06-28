<?php

class Lily_Facebook_Wrapper
{
	private $oauth_client;
	
	private $url_login = "https://www.facebook.com/dialog/oauth";
	private $url_access = "https://graph.facebook.com/oauth/access_token";
	private $url_root = "https://graph.facebook.com";
	
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
	
	public function getOauthLoginUrl($options=null) {
		if (null === $options) {
			$options = array();
		}
		if (!isset($options['client_id'])) {
			$options['client_id'] = Lily_Facebook_Manager::getAppId();
		}
		return $this->url_login . '?' . http_build_query($options);
	}
	
	public function getOauthAccessToken($options=null) {
		if (null === $options) {
			$options = array();
		}
		if (!isset($options['code'])) {
			throw new Exception("code not provided");
		}
		if (!isset($options['client_id'])) {
			$options['client_id'] = Lily_Facebook_Manager::getAppId();
		}
		if (!isset($options['client_secret'])) {
			$options['client_secret'] = Lily_Facebook_Manager::getSecret();
		}
		$request = new Lily_Http_Request();
		$request->setParams($options)
			->setMethod('get')
			->setUrl($this->url_access);
		$client = Lily_Http_Client::getInstance();
		$client->execute($request);
		return $request->getResult();
	}
	
	public function api($url, $params=null) {
		$request = new Lily_Http_Request();
		$request->setUrl($this->url_root . '/' . $url)
			->setParams($params);
		$client = Lily_Http_Client::getInstance();
		$client->execute($request);
		return $request->getResult();
	}
	
}

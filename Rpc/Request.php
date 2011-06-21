<?php

class Lily_Rpc_Request {

	public $resource;
	public $method;
	public $params;
	public $id;
	public $path;
	private $info;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $arg
	 * @return void
	 */
	public function __construct() {}

	/**
	 * setResource function.
	 *
	 * @access public
	 * @param mixed (string) $resource
	 * @return void
	 */
	public function setResource($resource){
		$this->resource = $resource;
		return $this;
	}

	/**
	 * getResource function.
	 *
	 * @access public
	 * @return void
	 */
	public function getResource() {
		return $this->resource;
	}

	/**
	 * setMethod function.
	 *
	 * @access public
	 * @param mixed (string) $method
	 * @return void
	 */
	public function setMethod($method) {
		$this->method = $method;
		return $this;
	}

	public function getMethod() {
		return $this->method;
	}

	/**
	 * addParam function.
	 *
	 * @access public
	 * @param mixed $arg
	 * @return void
	 */
	public function addParam($arg) {
		if (is_null($this->params)) {
			$this->params = array();
		}
		$this->params[] = $arg;
		return $this;
	}

	/**
	 * setParams function.
	 *
	 * @access public
	 * @param mixed $arg
	 * @return void
	 */
	public function setParams($arg) {
		$this->params = $arg;
		return $this;
	}

	/**
	 * getParams function.
	 *
	 * @access public
	 * @return void
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * setId function.
	 *
	 * @access public
	 * @param mixed $arg
	 * @return void
	 */
	public function setId($arg) {
		$this->id = $arg;
		return $this;
	}

	/**
	 * getId function.
	 *
	 * @access public
	 * @return void
	 */
	public function getId() {
		return $this->id;
	}
	
	public function setPath($arg) {
		$this->path = $arg;
		return $this;
	}
	
	public function getPath() {
		return $this->path;
	}

	/**
	 * toJson function.
	 *
	 * @access public
	 * @return void
	 */
	public function toJson() {
		// Necessary to prevent errors with PHP's json_encode
		$array = array(
			'resource'	=> $this->resource,
			'method'	=> $this->method,
			'params'	=> $this->params,
			'path'		=> $this->path,
			'id'		=> $this->id
		);
		$clean = Utility::fixEncoding($array);
		return json_encode($clean);
	}

	public function setInfo($info) {
		$this->info = $info;
	}

	public function getInfo() {
		return $this->info;
	}
}
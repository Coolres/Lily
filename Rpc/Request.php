<?php

/**
 * JSONRPC_Request class.
 * http://json-rpc.org/wiki/specification
 * @author Matt Ward
 */
class XMLRPC_Request {

	public $resource;
	public $method;
	public $params;
	public $id;
	private $info;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $arg
	 * @return void
	 */
	public function __construct($arg=NULL) {
		if (null !== $arg) {
			if (is_string($arg)) {
				$arg = json_decode($arg);
			}

			if (!is_object($arg)) {
				throw new JSONRPC_Exception("Could not decode request", $arg);
			}

			if (isset($arg->resource)) {
				$this->resource = $arg->resource;
			}

			if (!isset($arg->id)) {
				//throw new JSONRPC_Exception("Request if not specified");
			}
			$this->id = isset($arg->id) ? $arg->id : -1;

			if (!isset($arg->method)) {
				throw new JSONRPC_Exception("No method specified");
			}
			$this->method = $arg->method;

			if (isset($arg->params)) {
				$this->params = $arg->params;
			}
		}

	}

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
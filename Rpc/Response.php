<?php

/**
 * JSONRPC_Response class.
 *
 * @author Matt Ward
 */
class XMLRPC_Response
{
	public $result;
	public $error;
	public $id;


	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $arg
	 * @return void
	 */
	public function __construct($arg = NULL) {
		if (null !== $arg) {

			if (is_string($arg)) {
				$arg = json_decode($arg);
			}

			if (!is_object($arg)) {
				throw new XMLRPC_Exception("Could not decode specified response: " . PHP_EOL . print_r($arg, true));
			}

			if (isset($arg->error) && $arg->error !== null) {
				throw new XMLRPC_Exception("Request returned with an error, '{$arg->error}'");
			}

			if (!isset($arg->result)) {
				throw new XMLRPC_Exception("Result not specified: ". PHP_EOL . print_r($arg, true));
			}
			$this->result = $arg->result;

			if (!isset($arg->id)) {
				throw new XMLRPC_Exception("Response id not specified: ". PHP_EOL . print_r($arg, true));
			}
			$this->id = $arg->id;
		}

	}

	/**
	 * setResult function.
	 *
	 * @access public
	 * @param mixed $arg
	 * @return void
	 */
	public function setResult($arg) {
		$this->result = $arg;
		return $this;
	}

	/**
	 * getResult function.
	 *
	 * @access public
	 * @return void
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * setError function.
	 *
	 * @access public
	 * @param mixed $arg
	 * @return void
	 */
	public function setError($arg) {
		$this->error = $arg;
		return $this;
	}

	/**
	 * getError function.
	 *
	 * @access public
	 * @return void
	 */
	public function getError() {
		return $this->error;
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
		return json_encode($this);
	}
}
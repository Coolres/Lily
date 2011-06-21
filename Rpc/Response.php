<?php

/**
 * JSONRPC_Response class.
 *
 * @author Matt Ward
 */
class Lily_Rpc_Response
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
			if (isset($arg->error) && $arg->error !== null) {
				$this->error = $arg->error;
			}

			if (!isset($arg->result)) {
				$this->result = $arg->result;
			}
			

			if (!isset($arg->id)) {
				$this->id = $arg->id;
			}
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
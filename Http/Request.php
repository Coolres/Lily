<?php

/**
 * Http_Request
 * @author Matt Ward
 *
 */
class Lily_Http_Request
{
	protected $_url;
	protected $_params;
	protected $_result;
	protected $_info;
	protected $_method;

	public function __construct() {
		$this->_method = 'GET';
	}

	/**
	 * getUrl
	 * @return the $_url
	 */
	public function getUrl ()
	{
		return $this->_url;
	}

	/**
	 * setUrl
	 * @param field_type $_url
	 * @return Service_Adapter_Request
	 */
	public function setUrl ($_url)
	{
		$this->_url = $_url;
		return $this;
	}

	/**
	 * getParams
	 * @return the $_params
	 */
	public function getParams ()
	{
		return $this->_params;
	}

	/**
	 * setParams
	 * @param field_type $_params
	 * @return Service_Adapter_Request
	 */
	public function setParams ($_params)
	{
		$this->_params = $_params;
		return $this;
	}

	public function getParam($id) {
		if (isset($this->_params[$id])) {
			return $this->_params[$id];
		}
		return null;
	}

	/**
	 * getResult
	 * @return the $_result
	 */
	public function getResult ()
	{
		return $this->_result;
	}

	/**
	 * setResult
	 * @param field_type $_result
	 * @return Service_Adapter_Request
	 */
	public function setResult ($_result)
	{
		$this->_result = $_result;
		return $this;
	}

	public function setInfo ($info)
	{
		$this->_info = $info;
		return $this;
	}

	public function getInfo ()
	{
		return $this->_info;
	}

	public function getMethod() {
		return $this->_method;
	}

	public function setMethod($arg) {
		$this->_method = $arg;
		return $this;
	}

	public function __toString() {
		$result = $this->_url;
		if (!empty($this->_params)) {
			$result .= '?' . http_build_query($this->_params);
		}
		$result .= PHP_EOL;
		$result .= 'INFO:' . PHP_EOL . print_r($this->_info, true) . PHP_EOL;
		//$result .= 'RESULT:' . PHP_EOL . print_r($this->_result, true). PHP_EOL;
		return $result;
	}
}
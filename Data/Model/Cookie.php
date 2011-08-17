<?php


class Lily_Data_Model_Cookie
{
	protected $_name;
	protected $_value;
	protected $_ttl;
	protected $_dir;
	protected $_domain;
	
	public function setName($arg) {
		$this->_name = $arg;
		return $this;
	}

	public function getName() {
		return $this->_name;
	}
	
	public function setValue($arg) {
		$this->_value = $arg;
		return $this;
	}
	
	public function getValue() {
		return $this->_value;
	}
	
	public function setTTL($arg) {
		$this->_ttl = $arg;
		return $this;
	}
	
	public function getTTL() {
		return $this->_ttl;
	}
	
	public function setDirectory($dir) {
		$this->_dir = $dir;
		return $this;
	}
	
	public function getDirectory() {
		return $this->_dir;
	}
}

<?php

abstract class Lily_Rpc_Resource_Abstract
{
	protected $_meta;
	protected $_name;
	
	public function __construct($meta_info, $name) {
		$this->_meta_info = $meta_info;	
		$this->_name = $name;
			
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getMeta($procedure) {
		if (isset($this->_meta_info[$procedure])) {
			return $this->_meta_info[$procedure];
		}
		throw new Exception("Meta info for $procedure not defined");
		return null;
	}
	
	public function getMetaMethod($procedure) {
		if ($temp = $this->getMeta($procedure)) {
			if (isset($temp['method'])) {
				return $temp['method'];
			}
		}
		return null;
	}
}

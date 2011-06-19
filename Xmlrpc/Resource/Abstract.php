<?php

abstract class Lily_Xmlrpc_Resource_Abstract 
	extends Lily_Rpc_Resource_Abstract
{
	protected $_resource;
	
	public function __construct($meta) {
	// todo need to pass resources along, eg. the php file we are accessing	
	}
	
	public function getMeta($procedure) {
		if (isset($this->_meta_info[$procedure])) {
			return $this->_meta_info[$procedure];
		}
		throw new Exception("Meta info for $procedure not defined");
		return null;
	}
}

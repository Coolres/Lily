<?php
class Lily_Config_Exception extends Exception {
	
	
	public function __construct($directive, $code=0) {
		$message = "Configuration directive '$directive' not specified.";
		parent::__construct($message, $code);
	}
}

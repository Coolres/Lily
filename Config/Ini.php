<?php

// TODO WORK IN PROGRESS.
class LilypadMVC_Config_Ini
{

	protected $_file;
	
	
	public function __construct($ini_file)
	{
		$this->_file = $ini_file;
		
	}
	
	protected function _parse() 
	{
		if (!file_exists($this->_file)) 
		{
			throw new Exception("$this->_file cannot be found");	
		}
		
		$config = array();
		$handle = fopen($this->_file, 'r');
		while ($line = fgets($handle))
		{
			// TODO
		}
		
	}
	
	public function merge(LilypadMVC_Config_Ini& $config) {
		
	}
	
}
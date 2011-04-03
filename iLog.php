<?php

/**
 * 
 * @author Matt Ward
 *
 */
interface LilypadMVC_iLog
{
//	protected function writeError($message);
//	
//	protected function writeDebug($message);
	
	public function error($message, $object=null, $constant=null);

	public function debug($message, $object=null, $constant=null);
	
	public function handler($errno, $errstr, $errfile=NULL, $errline=NULL, $context=NULL);
}
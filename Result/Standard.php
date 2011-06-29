<?php


/**
 * Result_Abstract class.
 * Standard API that return objects should implement when sending data to the client. Allows back-end and front-end engineers to 
 * develop to a common return object.
 *
 * Vsibility is public for simplar json_encoding
 *
 * @author Matt Ward
 */
class Lily_Result_Standard {
	
	public 	$result;
	public 	$meta;
	public	$success;
	public  $error;
	
	
	public function __construct($result=NULL, $sucess=true) {
		$this->success	= $sucess;
		$this->result	= $result;
		$this->meta		= array();
	}
	
	public function addResult($result, $key=null) {
		if (null === $key)
			$this->result[]	= $result;
		else 
			$this->result[$key] = $result;
		return $this;
	}
	
	public function setResult($result) {
		$this->result	= $result;
		return $this;
	}
	
	public function setSuccess($success) {
		$this->success = (bool) $success;
		return $this;
	}
	
	public function setMessage($message) {
		$this->meta['message']	= $message;
		return $this;
	}
	
	public function setCount($count) {
		$this->meta['count']	= (int) $count;
		return $this;
	}

	public function setError($error) {
		$this->error = $error;
		return $this;
	}
	
	public function populate($object) {
		if (is_object($object)) {
			if (isset($object->success))
				$this->success = $object->success;
			if (isset($object->error))
				$this->error = $object->error;
			if (isset($object->result))
				$this->result = $object->result;
			if (isset($object->meta))
				$this->populateR($this->meta, $object->meta);
		} elseif (is_array($object)) {
			if (isset($object['success']))
				$this->success = $object['success'];
			if (isset($object['error']))
				$this->error = $object['error'];
			if (isset($object['result']))
				$this->result = $object['result'];
			if (isset($object['meta']))
				$this->populateR($this->meta, $object['meta']);
		}
	}
	
	private function populateR(&$target, $source) {
		foreach ($source as $prop => $value) {
			if (is_object($value) || is_array($value)) {
				$target[$prop] = array();
				$this->populateR($target[$prop], $value);
			} else {
				$target[$prop] = $value;
			}
		}
	}
}
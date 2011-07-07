<?php

/**
 * Abstract Model_Abstract class.
 *
 * @author Matt Ward
 * @abstract
 */
abstract class Lily_Data_Model_Abstract
{
	protected $_id;

	public function __construct($arg = NULL) {
		if (!empty($arg)) {
			$this->populate($arg);
		}
	}

	/**
	 * toArray function.
	 *
	 * @access public
	 * @abstract
	 * @return void
	 */
	public function __toArray ()
	{
		$vars = get_object_vars($this);
		$temp = array();
		foreach ($vars as $key => $value) {
			if ($value === null) continue;
			if (substr($key, 0, 1) == '_') {
				$key = substr($key, 1);
			}
			$temp[$key] = $value;
		}
		return $temp;
	}
	

	/**
	 * fromArray function.
	 *
	 * @access public
	 * @param mixed array& $array
	 * @return void
	 */
	public function __fromArray(array $array) {
		$this->populate($array);
		return $this;
	}

	/**
	 * isValid
	 * Validate the object according to the object's definition of valid
	 */
	public function __isValid() {
		return true;
	}

	/**
	 * isEqual function.
	 *
	 * @access public
	 * @param mixed Model_Abstract& $model
	 * @return bool
	 */
	public function __isEqual(Model_Abstract& $model) {
		if (get_class($this) !== get_class($model)) {
			return false;
		}

		$my_vars = get_object_vars($this);
		$their_vars = get_object_vars($model);
		if (count($my_vars) != count($their_vars)) {
			return false;
		}
		
		foreach ($my_vars as $key => $value) {
			if (!isset($model->$key)) return false;
			if ($model->$key != $value) return false;
		}
		
		return true;
	}

	public function getId() {
		return $this->_id;
	}
	
	public function setId($arg) {
		$this->_id = $arg;
		return $this;
	}

	/**
	 * populate function.
	 *
	 * @access public
	 * @param mixed &$data
	 * @return void
	 */
	public function populate(&$data) {
		if (empty($data) || (!is_array($data) && !is_object($data))) {
			return;
		}
		foreach ($data as $key => $value) {
			// If its something like 'ids[]' try to call the add function
			if ('[]' == substr($key, -2)) {
				$function = Utility::toCamelCase('add_' . substr($key, 0, -2));
			} else {
				$function = Utility::toCamelCase('set_' . $key);
			}

			if (method_exists($this, $function)) {
				$this->$function($value);
			}
		}
	}
}
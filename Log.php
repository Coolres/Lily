<?php

/**
 * Log class.
 * @author Matt Ward
 */
class Lily_Log {
	
	// For use with plugin pattern in LIlypad MVC
	private static $instance;
	
	private $roles = null;
	
	public function __construct($options) {
		if (self::$instance !== null) {
			throw new Exception("Instance of Lily_Log already instantiated");
		}
		
		set_error_handler("Lily_Log::handler", E_ALL);
		if (is_array($options)) {
			$this->roles = $options;
			foreach ($options as $role => $props) {
				if (!isset($props['location'])) {
					Lily_Log::write("warning", "log.$role.location not defined.");
				} else {
					self::initFile($props['location']);
				}
			}
		}
		self::$instance = $this;
	}
	
	public static function write($formatted_role, $desc, $object=null) {
		if (null === self::$instance) {
			throw new Exception("Log instance not configured");
		}
		$temp		= next(debug_backtrace(false));
		$message = '';
		self::buildStackTrace($temp, $message, false);
		$message .= $desc;
		if ($object) {
			$message .= print_r($object, true);
		}
		$message .= PHP_EOL;
		self::$instance->writeByRole($formatted_role, $message);		
	}
	/**
	 * Send a message to the debug log or screen dependent on environment
	 * @param	string		$message		Message to send
	 * @param	string		$object			Optional object to show string rep of
	 * @param	string		$constant		If there is a constant that should be check to determine to log output
	 */
	public static function debug($desc, $object=NULL, $constant=NULL, $logfile=NULL)
	{
		if (null === self::$instance) {
			throw new Exception("Log instance not configured");
		}
		$formatted_role = 'debug';
		$temp		= next(debug_backtrace(false));
		$message	= '';
		self::buildStackTrace($temp, $message, false);

		$message .= $desc;
		if ($object) {
			$message .= print_r($object, true);
		}
		$message .= PHP_EOL;
		self::$instance->writeByRole($formatted_role, $message);
	}
	
		/**
	 * Send a message to the debug log or screen dependent on environment
	 * @param	string		$message		Message to send
	 * @param	string		$object			Optional object to show string rep of
	 * @param	string		$constant		If there is a constant that should be check to determine to log output
	 */
	public static function error($desc, $object=NULL, $full_stack=false)
	{
		if (null === self::$instance) {
			throw new Exception("Log instance not configured");
		}
		$formatted_role = 'error';
		$temp		= next(debug_backtrace(false));
		$message = '';
		self::buildStackTrace($temp, $message, false);
		$message .= $desc;
		if ($object) {
			$message .= print_r($object, true);
		}
		if ($full_stack) {
			$message .= PHP_EOL . print_r(array_splice($temp, 1), true);
		}
		$message .= PHP_EOL;
		self::$instance->writeByRole($formatted_role, $message);
	}

	/**
	 * buildStackTrace function.
	 *
	 * @access private
	 * @static
	 * @param mixed &$stack
	 * @param mixed &$message
	 * @param bool $line_number. (default: true)
	 * @return void
	 */
	public static function buildStackTrace(&$stack, &$message, $line_number=true) {

		if (isset($stack['class']) && isset($stack['function'])) {
			$message .= $stack['class'] . $stack['type'] . $stack['function'];
		} else if (isset($stack['function'])) {
			$message .= $stack['function'];
		} else if (isset($stack['file'])) {
			$message .= $stack['file'];
		}

		// Not workign as expected. Will debug later...
		//if (isset($stack['line']) && $line_number) {
		//	$message .= '[' . $stack['line'] . ']';
		//}

		$message .= ':: ';
		return;
	}

	/**
	 * handler function.
	 * Conforms to php's error_handler definition. Used for routing all traffic to it
	 * @access public
	 * @static
	 * @param mixed $errno
	 * @param mixed $errstr
	 * @param mixed $errfile. (default: NULL)
	 * @param mixed $errline. (default: NULL)
	 * @param mixed $context. (default: NULL)
	 * @return void
	 */
	public static function handler($errno, $errstr, $errfile=NULL, $errline=NULL, $context=NULL)
	{
		if (null === self::$instance) {
			throw new Exception("Log instance not configured");
		}
		$formatted_role = 'error';
		$message = '';
		if ($errfile) {
			$message	.=  "[{$errfile}::{$errline}] ";
		}
		$message .= $errstr;
		if ($context) {
			//$message .= PHP_EOL . print_r($context, true);
		}
		$message .= PHP_EOL;
		self::$instance->writeByRole($formatted_role, $message);
	}

	public static function initFile($filename) {
		if ($filename == 'STDOUT') return;
		$file = fopen($filename, 'a');
		if ($file === false) {
			throw new Exception("Could not open log file {$filename} for write.");
		} else {
			fclose($file);
		}
	}

	public static function getDebugBacktrace($NL = "\n") {
		ob_start();
        debug_print_backtrace();
    	$trace = ob_get_contents();
    	ob_end_clean();
		return $trace;
	}

	/**
	 * Only works in php 5.3
	 */
	public static function __callStatic($method, $arguments) {
		if (null === self::$instance) {
			throw new Exception("Log instance not configured");
		}
		$formatted_role = Utility::fromCamelCase($method);
		$temp		= next(debug_backtrace(false));
		$message = isset($arguments[0]) ? $arguments[0] : '';
		self::buildStackTrace($temp, $message, false);
		if (isset($arguments[1])) {
			$message .= print_r($arguments[1], true);
		}
		$message .= PHP_EOL;
		$instance->writeByRole($method, $message);
	}
	
	public function __call($method, $arguments) {
		$formatted_role = Utility::fromCamelCase($method);
		$temp		= next(debug_backtrace(false));
		$message = isset($arguments[0]) ? $arguments[0] : '';
		self::buildStackTrace($temp, $message, false);
		if (isset($arguments[1])) {
			$message .= print_r($arguments[1], true);
		}
		$message .= PHP_EOL;
		$this->writeByRole($method, $message);
	}
	
	public function writeByRole($role, $message) {
		if (!isset($this->roles[$role])) {
			if (isset($this->roles['warning']) 
				&& isset($this->roles['warning']['location'])) {
				Lily_Log::write("warning", "log.$role not defined.");
			}
			return;
		}
		$props = $this->roles[$role];
		if (!isset($props['location'])) {
			if (isset($this->roles['warning']) 
				&& isset($this->roles['warning']['location'])) {
				Lily_Log::write("warning", "log.$role.location not defined.");
			}
			return;
		}
		$message	= '['.date('Y-m-d H:i:s e', time()).'][' . strtoupper($role) .']' . $message;
		$file = $props['location'];
		$enabled = isset($props['enabled']) ? $props['enabled'] : false;
		if ((bool)$enabled == true) {
			error_log($message, 3, $file);
		}
	}
}
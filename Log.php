<?php

/**
 * Log class.
 * @author Matt Ward
 */
class Log{

	/**
	 * Send a message to the debug log or screen dependent on environment
	 * @param	string		$message		Message to send
	 * @param	string		$object			Optional object to show string rep of
	 * @param	string		$constant		If there is a constant that should be check to determine to log output
	 */
	public static function debug($desc, $object=NULL, $constant=NULL, $logfile=NULL)
	{
		
		
		// Check if the debug constant specified is defined and turned to true
		if (!is_null($constant)){ 
			if (!defined($constant) || (defined($constant) && constant($constant) == false)) {
				return false;
			}
		} else {
			// Dont post things unless configured to do so...
			if (!defined('LOG_DEBUG_OUTPUT') || constant('LOG_DEBUG_OUTPUT') == false) {
				return false;
			}
		}
		
		$temp		= next(debug_backtrace(false));
		$message	= '['.date('Y-m-d H:i:s', time()).'][DEBUG]';
		self::buildStackTrace($temp, $message, false);
	
		$message.= $desc;
		if (!is_null($object)) {
			$message .= PHP_EOL.print_r($object, true);
		}
		
		if ($logfile) {
			error_log($message.PHP_EOL, 3, $logfile);
		} else if (defined('DEBUG_LOG')) {
			error_log($message.PHP_EOL, 3, constant('DEBUG_LOG'));
		} else {
			error_log($message);
		}
	}

	/**
	 * error function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $desc
	 * @param mixed $object. (default: NULL)
	 * @param bool $full_stack print the full stack
	 * @return void
	 */
	public static function error($desc, $object=NULL, $full_stack=false) {
		// TODO add some special flag for errors, maybe special log
	
		$back_trace = debug_backtrace(false);
		$temp		= next($back_trace);
		$message	= '[ERROR]';
		self::buildStackTrace($temp, $message, true);
	
		$message.= $desc;
		if (!is_null($object)) {
			$message .= PHP_EOL . print_r($object, true);
		}
		
		if ($full_stack) {
			$message .= PHP_EOL . print_r(array_splice($back_trace, 1), true);
		}
		
		if (defined('ERROR_LOG')) {
			error_log($message.PHP_EOL, 3, constant('ERROR_LOG'));
		} else {
			error_log($message);
		}
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
		$cooked = "[ERROR] ";
		if ($errfile) {
			$cooked .= $errfile . '::' . $errline . " ";
		}
		$cooked .= $errstr;
  		
  		if ($context) {
  			$cooked . PHP_EOL . print_r($context, true);
  		}
  		$cooked .= PHP_EOL;
  
		error_log($cooked, 3, constant('ERROR_LOG'));	
	}
	
	/**
	 * registerErrorHandler
	 * 
	 * @throws Exception
	 */
	public static function registerErrorHandler() 
	{
		$log = constant("ERROR_LOG");
		if (!empty($log)) {
			if (!file_exists($log)) {
				$file = fopen($log, 'w');
				fclose($file);	
			}
			set_error_handler("Log::handler",E_ALL);
		} else {
			throw new Exception("trying to figure out what path this gets called without errorlog being defined");
		}
	}
	
	/**
	 * registerDebugHandler
	 * 
	 */
	public static function registerDebugHandler() {
		$log = constant("DEBUG_LOG");
		if (!file_exists($log)) {
			$file = fopen($log, 'w');
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
}
<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in
 * the project root's LICENSE file.
 */
/**
 *
 * LilypadMVC_Log
 * @author Matt Ward
 *
 */
class LilypadMVC_Log implements LilypadMVC_iLog
{
	protected $_error_log;
	protected $_debug_log;
	protected $_log_debug_output;

	public function __construct($options = null) {
		if (null !== $options) {
			if (isset($options['error_log'])) {
				$this->_error_log = $options['error_log'];
			}

			if (isset($options['debug_log'])) {
				$this->_debug_log = $options['debug_log'];
			}
		}
	}

	protected function writeError($message) {
		if ($this->_error_log) {
			error_log($message.PHP_EOL, 3, $this->_error_log);
		} else {
			error_log($message);
		}
	}

	protected function writeDebug($message) {
		if ($this->_debug_log) {
			error_log($message.PHP_EOL, 3, $this->_debug_log);
		}
	}

	public function error($message, $object=null, $constant=null) {
		if ($constant && defined($constant) && !constant($constant)) {
			return;
		}

		$trace = debug_backtrace(false);
		$temp = next($trace);
		self::buildStackTrace($temp, $message);

		if (null !== $object) {
			$message .= PHP_EOL . print_r($object, true);
		}
		$this->writeError($message);
	}

	public function debug($message, $object=null, $constant=null) {
		if ($constant && defined($constant) && !constant($constant)) {
			return;
		}

		$trace = debug_backtrace(false);
		$temp = next($trace);
		self::buildStackTrace($temp, $message);

		if (null !== $object) {
			$message .= PHP_EOL . print_r($object, true);
		}
		$this->writeDebug($message);
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

		$temp = '';
		if (isset($stack['class']) && isset($stack['function'])) {
			$temp = $stack['class'] . $stack['type'] . $stack['function'];
		} else if (isset($stack['function'])) {
			$temp .= $stack['function'];
		} else if (isset($stack['file'])) {
			$temp .= $stack['file'];
		}

		// Not workign as expected. Will debug later...
		//if (isset($stack['line']) && $line_number) {
		//	$message .= '[' . $stack['line'] . ']';
		//}

		$message = $temp.':: ' . $message;
	}

	public function handler($errno, $errstr, $errfile=NULL, $errline=NULL, $context=NULL)
	{
		$cooked = '[ERROR]';
		if ($errfile) {
			$cooked .= $errfile . '::' . $errline . " ";
		}
		$cooked .= $errstr;

		$this->error($cooked);
	}

}
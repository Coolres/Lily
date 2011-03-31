<?php

/**
 * Lilypad_Controller_Exception class.
 * @author Matt Ward
 * @extends Exception
 */
class Lilypad_Controller_Exception extends Exception {
    private $error_code;
    
    public function __construct($message, $errorcode=500)
    {
        $this->error_code = $errorcode;
        parent::__construct($message);
    }
    
    
    public function getErrorCode()
    {
        return $this->error_code;
    }
}

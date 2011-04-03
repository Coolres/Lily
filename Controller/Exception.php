<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in 
 * the project root's LICENSE file. 
 */
/**
 * LilypadMVC_Controller_Exception class.
 * @author Matt Ward
 * @extends Exception
 */
class LilypadMVC_Controller_Exception extends Exception {
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

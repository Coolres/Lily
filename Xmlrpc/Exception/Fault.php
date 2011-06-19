<?php

class Lily_Xmlrpc_Exception_Fault extends Exception 
{
    private $faultCode;
    private $faultString;

    function __construct($faultCode, $faultString)
    {
        parent::__construct("XMLRPC fault [$faultCode] $faultString");
    }

    function getFaultCode()
    {
        return $this->faultCode;
    }

    function getFaultString()
    {
        return $this->faultString;
    }
}
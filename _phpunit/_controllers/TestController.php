<?php

class TestController extends Lilypad_Controller_Action
{
    public function indexAction()
    {
        echo __FUNCTION__;
    }
    
    
    public function helloAction()
    {
        echo __FUNCTION__ . "Hello World!";
    }

}

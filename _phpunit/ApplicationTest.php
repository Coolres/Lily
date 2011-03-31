<?php
require_once('PHPUnit/Framework.php');

class TestApplication extends PHPUnit_Framework_TestCase
{
    private $application;

	public function __construct()
    {
    	// Not ideal with the realtive paths, but eh
    	require_once(dirname(dirname(__FILE__)) . '/Lilypad/Application.php');
    	Lilypad_Application::getAutoloader();
    }

    public function setUp()
    {
        $options = array(
            'controller_dirs' => array(
                'default'=> dirname(__FILE__) . '/_controllers'
            )
        );

        $this->application = new Lilypad_Application($options);
    }


    public function testIndexDispatch()
    {
        $this->application->run('/test');

        $this->application->run('/test/hello');
    }
}

<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once(dirname(__FILE__) . '/../../../../config/boot.php');
require_once(LIB_DIR . '/Lilypad/Application.php');

$loader = Lilypad_Application::getAutoloader();
/**
 * test case.
 */
class TestIni extends PHPUnit_Framework_TestCase
{
	private $file;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp();
		$this->file = dirname(__FILE__) . '/config.ini';
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated TestIni::tearDown()
		parent::tearDown();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{
		// TODO Auto-generated constructor
	}
	
	public function testParse() {
		$ini_array = parse_ini_file($this->file, true);
		print_r($ini_array);
	}
}


<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_LoggerTest extends PHPUnit_Framework_TestCase
{
    private $_logger;
    
    public function setUp()
    {
        $this->_logger = PHPFrame_Logger::instance();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_a()
    {
    	print_r($this->_logger);
    	exit;
    }
}

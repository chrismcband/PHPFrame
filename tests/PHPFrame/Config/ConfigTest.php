<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_ConfigTest extends PHPUnit_Framework_TestCase
{
    private $_config;
    
    public function setUp()
    {
    	$config_file   = PEAR_Config::singleton()->get("data_dir");
    	$config_file  .= DS."PHPFrame".DS."etc".DS."phpframe.ini";
        $this->_config = new PHPFrame_Config($config_file);
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_getIterator()
    {
        $array = iterator_to_array($this->_config);
        
        $this->assertArrayHasKey("app_name", $array);
        $this->assertArrayHasKey("version", $array);
        $this->assertArrayHasKey("base_url", $array);
        $this->assertArrayHasKey("theme", $array);
        $this->assertArrayHasKey("default_lang", $array);
        $this->assertArrayHasKey("secret", $array);
        $this->assertArrayHasKey("timezone", $array);
        $this->assertArrayHasKey("default_controller", $array);
        $this->assertArrayHasKey("ignore_acl", $array);
    }
    
    public function test_get()
    {
        
    }
    
    public function test_set()
    {
        
    }
    
    public function test_bind()
    {
        $app_name  = $this->_config->get("app_name");
        $imap_pass = $this->_config->get("imap.pass");
        
        $array = array("app_name"=>"New app name", "imap.pass"=>"somepassword");
        $this->_config->bind($array);
        
        $app_name_updated  = $this->_config->get("app_name");
        $imap_pass_updated = $this->_config->get("imap.pass");
        
        $this->assertNotEquals($app_name, $app_name_updated);
        $this->assertNotEquals($imap_pass, $imap_pass_updated);
    }
    
    public function test_getSections()
    {
        $sections = $this->_config->getSections();
        $this->assertType("array", $sections);
        
        $this->assertContains("general", $sections);
        $this->assertContains("filesystem", $sections);
        $this->assertContains("debug", $sections);
        $this->assertContains("sources", $sections);
        $this->assertContains("db", $sections);
        $this->assertContains("smtp", $sections);
        $this->assertContains("imap", $sections);
    }
    
    public function test_getKeys()
    {
        $keys = $this->_config->getKeys();
        $this->assertType("array", $keys);
        
        $this->assertContains("app_name", $keys);
        $this->assertContains("version", $keys);
        $this->assertContains("base_url", $keys);
        $this->assertContains("theme", $keys);
        $this->assertContains("default_lang", $keys);
        $this->assertContains("secret", $keys);
        $this->assertContains("timezone", $keys);
        $this->assertContains("default_controller", $keys);
        $this->assertContains("ignore_acl", $keys);
        
        $this->assertContains("debug.display_exceptions", $keys);
        $this->assertContains("debug.log_level", $keys);
    }
    
    public function test_keyExists()
    {
        
    }
    
    public function test_store()
    {
        
    }
}

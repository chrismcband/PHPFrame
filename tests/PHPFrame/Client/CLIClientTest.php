<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_CLIClientTest extends PHPUnit_Framework_TestCase
{
    private $_client;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_client = new PHPFrame_CLIClient();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_getName()
    {
        $this->assertEquals("cli", ($this->_client->getName()));
    }
    
    public function test_detect()
    {
        $this->assertType("PHPFrame_CLIClient", PHPFrame_CLIClient::detect());
    }
    
    public function test_populateRequest()
    {
        $request = new PHPFrame_Request();
        
        $this->assertType("array", $request->getParams());
        $this->assertEquals(0, count($request->getParams()));
        
        $script_name = $request->getScriptName();
        $this->assertTrue(empty($script_name));
        
        $request_time = $request->getRequestTime();
        $this->assertTrue(empty($request_time));
        
        // Populate the request
        $this->_client->populateRequest($request);
        
        // Now check that we got some values
        $this->assertType("array", $request->getParams());
        $this->assertEquals(1, count($request->getParams()));
        
        $script_name = $request->getScriptName();
        $this->assertTrue(!empty($script_name));
        
        $request_time = $request->getRequestTime();
        $this->assertType("int", $request_time);
    }
    
    public function test_prepareResponse()
    {
        $response = new PHPFrame_Response();
        $response->document(new PHPFrame_XMLDocument());
        $response->renderer(new PHPFrame_XMLRenderer());
        
        $this->assertType("PHPFrame_XMLDocument", $response->document());
        $this->assertType("PHPFrame_XMLRenderer", $response->renderer());
         
        $this->_client->prepareResponse($response, "");
        
        $this->assertType("PHPFrame_PlainDocument", $response->document());
        $this->assertType("PHPFrame_PlainRenderer", $response->renderer());
        
    }
}

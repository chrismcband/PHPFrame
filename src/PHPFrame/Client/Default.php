<?php
/**
 * PHPFrame/Client/Default.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Default.php 1057 2014-06-02 11:51:42Z chrismcband@gmail.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Client used by default (PC HTTP browsers or anything for which no helper exists)
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Client_Default implements PHPFrame_Client_IClient
{
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @static
     * @access public
     * @return PHPFrame_Client_IClient|boolean Object instance of this class if correct
     *                                         helper for client or false otherwise.
     */
    public static function detect() 
    {
        //TODO test checking for $_SERVER['HTTP_USER_AGENT']
        
        //this is our last hope to find a helper, just return instance
        return new self;
    }
    
    /**    
     * Get client name
     * 
     * @access public
     * @return string Name to identify helper type
     */
    public function getName() 
    {
        return "default";
    }
    
    /**    
     * Populate the Unified Request array
     * 
     * @access public
     * @return array  Unified Request Array
     */
    public function populateRequest() 
    {
        $request = array();
        
        // Get an instance of PHP Input filter
        $inputfilter = new InputFilter();

        // Process incoming request arrays and store filtered data in class
        $request['request'] = $inputfilter->process($_REQUEST);
        $request['get'] = $inputfilter->process($_GET);
        $request['post'] = $inputfilter->process($_POST);

        //if request is for api controller handle raw post body
        if (isset($_REQUEST['component']) && $_REQUEST['component'] == 'com_api'
            && ($_SERVER['REQUEST_METHOD'] == 'POST'
                || $_SERVER['REQUEST_METHOD'] == 'PUT')
        ) {
            $input = file_get_contents("php://input");
            $post = json_decode($input, true);

            if (is_array($post)) {
                //add this post body to the our model of request
                $request['request'] = array_merge($request['request'], $post);
            }
        }

        // Once the superglobal request arrays are processed we unset them
        // to prevent them being used from here on
        unset($_REQUEST, $_GET, $_POST);
        
        return $request;
    }
    
    /**
     * Prepare response
     * 
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do 
     * something before the component is executed.
     * 
     * @param PHPFrame_Application_Response $response The response object to prepare.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function prepareResponse(PHPFrame_Application_Response $response) 
    {
        //check if the controller exists, return 404 if not
        $component_name = PHPFrame::Request()->getComponentName();

        $class_name = substr($component_name, 4)."Controller";
        if (!class_exists($class_name)) {
            $this->notFound();
        }
        
        $document = new PHPFrame_Document_HTML();
        
        // Set document as response content
        $response->setDocument($document);
    }

    public function notFound()
    {
        header('HTTP/1.0 404 Not Found');
        readfile('404.html');
        exit;
    }
    
    public function redirect($url)
    {
        $url = (string) trim($url);
        
        if ($url) {
            $url = PHPFrame_Utils_Rewrite::rewriteURL($url, false);
            header("Location: ".$url);
            exit;
        }
    }
}

<?php
/**
 * PHPFrame/Client/Mobile.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Mobile.php 1056 2014-05-29 13:07:06Z chrismcband@gmail.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Client for Mobile Devices
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Client_Mobile implements PHPFrame_Client_IClient
{
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @static
     * @access    public
     * @return    PHPFrame_Client_IClient|boolean    Object instance of this class if correct helper for client or false otherwise.
     */
    public static function detect() 
    {
        
        if (isset($_SERVER["HTTP_X_WAP_PROFILE"])) {
            return new self;
        }
        
        if (isset($_SERVER["HTTP_ACCEPT"]) 
            && preg_match("/wap\.|\.wap/i",$_SERVER["HTTP_ACCEPT"])
        ) { 
            return new self;
        }
        
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
        
            if (preg_match("/Creative\ AutoUpdate/i",$_SERVER["HTTP_USER_AGENT"])) {
                return new self;
            }
        
            $uamatches = array("midp", "j2me", "avantg", "docomo", "novarra", "palmos", "palmsource", "240x320", "opwv", "chtml", "pda", "windows\ ce", "mmp\/", "blackberry", "mib\/", "symbian", "wireless", "nokia", "hand", "mobi", "phone", "cdm", "up\.b", "audio", "SIE\-", "SEC\-", "samsung", "HTC", "mot\-", "mitsu", "sagem", "sony", "alcatel", "lg", "erics", "vx", "NEC", "philips", "mmm", "xx", "panasonic", "sharp", "wap", "sch", "rover", "pocket", "benq", "java", "pt", "pg", "vox", "amoi", "bird", "compal", "kg", "voda", "sany", "kdd", "dbt", "sendo", "sgh", "gradi", "jb", "\d\d\di", "moto");
        
            foreach ($uamatches as $uastring) {
                if (preg_match("/".$uastring."/i",$_SERVER["HTTP_USER_AGENT"])) {
                    return new self;
                }
            }
        
        }
        return false;
    }
    
    /**    
     * Get client name
     * 
     * @access    public
     * @return    string    Name to identify helper type
     */
    public function getName() 
    {
        return "default";
    }
    
    /**    
     * Populate the Unified Request array
     * 
     * @access    public
     * @return    array    Unified Request Array
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
        if ($_REQUEST['component'] == 'com_api' && ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT')) {
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
        // add the jQuery + jQuery UI libraries to the HTML document
        // that we will use in the response. jQuery lib need to be loaded before 
        // we load the jQuery plugins in the component output.
        
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
            $url = PHPFrame_Utils_Rewrite::rewriteURL($url);
            header("Location: ".$url);
            exit;
        }
    }
}

<?php
/**
 * PHPFrame/Utils/Rewrite.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Utils
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Rewrite.php 1065 2015-01-27 13:39:15Z chrismcband $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Rewrite Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Utils
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Utils_Rewrite
{
    /**
     * Rewrite the request
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function rewriteRequest() 
    {
        // If there is no request uri (ie: we are on the command line) we do not rewrite
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }
        
        // Get path to script
        $path = substr($_SERVER['SCRIPT_NAME'], 0, (strrpos($_SERVER['SCRIPT_NAME'], '/')+1));
        
        // If the script name doesnt appear in the request URI we need to rewrite
        if (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === false
            && $_SERVER['REQUEST_URI'] != $path
            && $_SERVER['REQUEST_URI'] != $path."index.php") {
            // Remove path from request uri. 
            // This gives us the component and action expressed as directories
            if ($path != "/") {
                $params = str_replace($path, "", $_SERVER['REQUEST_URI']);
            }
            else {
                // If app is in web root we simply remove preceding slash
                $params = substr($_SERVER['REQUEST_URI'], 1);
            }
            
            //preg_match('/^([a-zA-Z]+)\/?([a-zA-Z_]+)?\/?.*$/', $params, $matches);
            
            // Get component name using regex
            preg_match('/^([a-zA-Z]+)/', $params, $component_matches);
            
            // Get action name using regex
            preg_match('/^[a-zA-Z]+\/([a-zA-Z_]+)/', $params, $action_matches);
            
            if (isset($component_matches[1]) && !empty($component_matches[1])) {
                $component = "com_".$component_matches[1];
                if (isset($action_matches[1])) {
                    $action = $action_matches[1];
                }

                if ($component == 'com_roommanager' && $action == 'load_room') {
                    // Get roomid using regex
                    preg_match('/^[a-zA-Z]+\/[a-zA-Z_]+\/(\d+)/', $params, $roomid_matches);
                    if (isset($roomid_matches[1])) {
                        $_REQUEST['roomid'] = $roomid_matches[1];
                        $_GET['roomid'] = $roomid_matches[1];
                    }
                } else if ($component == 'com_home') {
                    //ignore any action for com_home controller, this is mostly
                    //backbone which handles push state urls
                    unset($action);
                }

                if ($component == 'com_api') {
                    if (isset($action) && $action == 'rooms') {
                        if (preg_match('/^api\/rooms\/(\d+)\/([a-zA-Z_]+)(?:\/(\d+))?(?:\/([a-zA-Z\/]+))?/', $params, $noteid_matches)) {
                            $_REQUEST['roomid'] = $noteid_matches[1];
                            $_GET['roomid'] = $noteid_matches[1];
                            $action = $noteid_matches[2];
                            if (isset($noteid_matches[3])) {
                                $_REQUEST['id'] = $noteid_matches[3];
                                $_GET['id'] = $noteid_matches[3];
                            }
                            if (isset($noteid_matches[4])) {
                                $_REQUEST['subpath'] = $noteid_matches[4];
                                $_GET['subpath'] = $noteid_matches[4];
                            }
                        } else if (preg_match('/^api\/rooms\/(\d+)/', $params, $id_matches)){
                            if (isset($id_matches[1])) {
                                $_REQUEST['id'] = $id_matches[1];
                                $_GET['id'] = $id_matches[1];
                            }
                        }
                    } else if (isset($action) && ($action == 'notes' ||
                        $action == 'messages' || $action == 'work') ||
                        $action == 'members' || $action == 'users' ||
                        $action == 'rooms' || $action == 'invites' ||
                        $action == 'requests' || $action == 'icons' ||
                        $action == 'events' || $action == 'contextviews' ||
                        $action == 'questions' || $action == 'questionchoices' ||
                        $action == 'questionanswers' || $action == 'contacts' ||
                        $action == 'contactlists'
                    ) {
                        preg_match('/^api\/(notes|messages|work|members|users|contacts|contactlists|invites|requests|icons|events|contextviews|questions|questionchoices|questionanswers)\/(\d+)/', $params, $noteid_matches);
                        if (isset($noteid_matches[2])) {
                            $_REQUEST['id'] = $noteid_matches[2];
                            $_GET['id'] = $noteid_matches[2];
                        } else {
                            preg_match('/^api\/(notes|messages|work|members|users|contacts|contactlists|invites|requests|icons|events|contextviews|questions|questionchoices|questionanswers)\/([a-zA-Z\/\_]+)/', $params, $subpath_matches);
                            if (isset($subpath_matches[2])) {
                                $_REQUEST['subpath'] = $subpath_matches[2];
                                $_GET['subpath'] = $subpath_matches[2];
                            }
                        }

                        preg_match('/^api\/(notes|messages|work|members|users|contacts|contactlists|invites|requests|icons|events|contextviews|questions|questionchoices|questionanswers)\/(\d+)\/([a-zA-Z\/]+)/', $params, $path_matches);
                        if (isset($path_matches[3])) {
                            $_REQUEST['subpath'] = $path_matches[3];
                            $_GET['subpath'] = $path_matches[3];
                        }

                    } else if (isset($action) && ($action == "world_settings")) {
                        preg_match('/^api\/world_settings\/(\w+)/', $params, $key_matches);
                        if (isset($key_matches[1])) {
                            $_REQUEST['key'] = $key_matches[1];
                            $_GET['key'] = $key_matches[1];
                        }
                    }
                }

                // Prepend component and action to query string
                $rewritten_query_string = "component=".$component;
                if (!empty($action)) $rewritten_query_string .= "&action=".$action;
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $rewritten_query_string .= "&".$_SERVER['QUERY_STRING'];
                }
                $_SERVER['QUERY_STRING'] = $rewritten_query_string;
                
                // Update request uri
                $_SERVER['REQUEST_URI'] = $path."index.php?".$_SERVER['QUERY_STRING'];

                // Set vars in _REQUEST array
                if (!empty($component)) {
                    $_REQUEST['component'] = $component;
                    $_GET['component'] = $component;
                }
                if (!empty($action)) {
                    $_REQUEST['action'] = $action;
                    $_GET['action'] = $action;
                }
            }
        }
    }
    
    /**
     * Rewrite URL
     * 
     * @param string $url   The URL to rewrite
     * @param bool   $xhtml A boolean to indicate whether we want to use an XHTML
     *                      compliant URL. Default value is TRUE.
     * 
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function rewriteURL($url, $xhtml=true) 
    {
        $uri = new PHPFrame_Utils_URI();

        if (!preg_match('/^http(s?)/i', $url)) {
            $url = $uri->getBase().$url;
        }

        // Parse URL string
        $url_array = parse_url($url);
        $query_array = array();
        if (isset($url_array['query'])) {
            parse_str($url_array['query'], $query_array);
        }
        
        
        // If there are no query parameters we don't need to rewrite anything
        if (count($query_array) == 0) return $url;
        
        $rewritten_url = "";
        
        if (isset($query_array['component']) && !empty($query_array['component'])) {
            $rewritten_url .= substr($query_array['component'], 4);
            unset($query_array['component']);
        }
        
        if (isset($query_array['action']) && !empty($query_array['action'])) {
            $rewritten_url .= "/".$query_array['action'];
            if ($query_array['action'] == 'load_room' && isset($query_array['roomid']) && !empty($query_array['roomid'])) {
                $rewritten_url .= "/".$query_array['roomid'];
                unset($query_array['roomid']);
            }
            unset($query_array['action']);
        }


        if (is_array($query_array) && count($query_array) > 0) {
            $rewritten_url .= "?";
            $i=0;
            foreach ($query_array as $key=>$value) {
                if ($i>0) $rewritten_url .= $xhtml ? "&amp;" : "&"; 
                $rewritten_url .= $key."=".urlencode($value);
                $i++;
            }
        }
        $new_base = $uri->getBase();
        if (substr($url, 0, 5) == 'https'){
        	$new_base = str_replace('http:', 'https:', $new_base);
        } else if (substr($url, 0, 4) == 'http'){
        	$new_base = str_replace('https:', 'http:', $new_base);
        }
        return $new_base.$rewritten_url;
    }
}

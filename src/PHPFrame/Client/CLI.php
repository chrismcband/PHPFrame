<?php
/**
 * PHPFrame/Client/CLI.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Client for Command Line Interface
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Client_CLI implements PHPFrame_Client_IClient
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
        
        global $argv;
        if (is_array($argv)) {
            return new self;
        }
        return false;
    }
    
    /**    
     * Populate the Unified Request array
     * 
     * @access    public
     * @return    array    Unified Request Array
     */
    public function populateURA() 
    {

        // Get arguments passed via command line and parse them as request vars
        global $argv;
        $request = array();
        for ($i=1; $i<count($argv); $i++) {
            if (preg_match('/^(.*)=(.*)$/', $argv[$i], $matches)) {
                $request['request'][$matches[1]] = $matches[2];
            }
        }
        return $request;
    }
    
    /**    
     * Get helper name
     * 
     * @access    public
     * @return    string    Name to identify helper type
     */
    public function getName() 
    {
        return "cli";
    }
    
    /**
     * Pre action hook
     * 
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do 
     * something before the component is executed.
     * 
     * @return    void
     */
    public function preActionHook() 
    {
        // Automatically log in as system user
        $user = new PHPFrame_User();
        $user->set('id', 1);
        $user->set('groupid', 1);
        $user->set('username', 'system');
        $user->set('firstname', 'System');
        $user->set('lastname', 'User');
        
        // Store user detailt in session
        $session = PHPFrame::getSession();
        $session->setUser($user);
    }
    
    /**
     * Render component view
     * 
     * This method is invoked by the views and renders the ouput data in the format specified
     * by the client.
     * 
     * @param    array    $data    An array containing the data assigned to the view.
     * @return    void
     */
    public function renderView($data) 
    {
        var_dump($data);
    }
    
    /**
     * Render overall template
     *
     * @param    string    &$str    A string containing the component output.
     * @return    void
     */
    public function renderTemplate(&$str) {}
}
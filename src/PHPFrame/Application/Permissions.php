<?php
/**
 * PHPFrame/Application/Permissions.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Permissions.php 1061 2014-07-08 22:53:30Z chrismcband $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Permissions Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Application_Permissions
{
    /**
     * Access level list loaded from database.
     * 
     * @var array
     */
    private $_acl=array();
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        // Load ACL from DB
//        $this->_acl = $this->_loadACL(PHPFrame::Session()->getUser());
    }
    
    /**
     * Authorise action in a component for a given user group
     * 
     * @param string $component The component we want to authorise
     * @param string $action    The action we want to authorise
     * @param int    $groupid   The groupid of the user we want to authorise
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function authorise($component, $action) 
    {
        $user = PHPFrame::Session()->getUser();

//        PHPFrame_Debug_Logger::write("Checking permission for $component.$action for userid ".PHPFrame::Session()->getUserId()." groupid ".PHPFrame::Session()->getGroupId());

        //check session user perm actions still valid
        $perm_session_user = PHPFrame::Session()->get('perms_acl_userid', 0);
        $perm_session_group = PHPFrame::Session()->get('perms_acl_groupid', 0);
        
//        PHPFrame_Debug_Logger::write("perm_session_user is $perm_session_user, perm_session_group is $perm_session_group");
        if (empty($this->_acl) || $perm_session_user != PHPFrame::Session()->getUserId()
            || $perm_session_group != PHPFrame::Session()->getGroupId()){
            $this->_acl = $this->_loadACL(PHPFrame::Session()->getUser());
            PHPFrame::Session()->set('perms_acl_userid', PHPFrame::Session()->getUserId());
            PHPFrame::Session()->set('perms_acl_groupid', PHPFrame::Session()->getGroupId());
        }

        // check if additional flags are present to skip permission checks
        try{
            if ($user->is_staff || $user->is_superuser) {
                return true;
            }
        } catch (PHPFrame_Exception $e) {
            // this portal must not have the additional user flags installed, proceed to do permissions check
        }

//        PHPFrame_Debug_Logger::write("Allowed: ".print_r((isset($this->_acl[$component])
//            && array_search($action, $this->_acl[$component]) !== FALSE), true));
        
        return (isset($this->_acl[$component]) 
            && array_search($action, $this->_acl[$component]) !== FALSE);
    }
    
    /**
     * Returns an 2d associative array list, containing all component actions that 
     * are allowed to be executed by this user. If the component action does 
     * not appear in the array list then the user is not allowed to run it.
     * 
     * Each entry in the associative array list is indexed by the component, 
     * the second dimension of the array is an array containing all the actions 
     * of the component the user can run. E.g.:
     * <code>$actions['com_roommanager'][0] = 'save_room';
     * $actions['com_roommanager'][1] = 'get_rooms';
     * $actions['com_exhibition'][0] = 'index';
     * $actions['com_exhibition'][1] = 'load';</code>
     * 
     * @param PHPFrame_User $user the user to check
     */
    private function _loadACL(PHPFrame_User $user) 
    {
        $db = PHPFrame::DB();
        
        $groupid = $user->groupid;
        
        if ($groupid == 0){ //change groupid to -1 for unauthenticated user
            $groupid = -1;
        }
        
        $q = "SELECT component, action FROM #__permission_actions_map AS pap";
        $q .= " JOIN #__permission_map pm ON pm.permissionid = pap.permissionid";
        $q .= " WHERE (pm.map_type = 'group' AND entityid = :groupid)";
        $q .= " OR (pm.map_type = 'user' AND entityid = :userid)";
        
        $results = $db->fetchAssocList($q, array(':groupid'=>$groupid,
            ':userid'=>$user->id));

        $components = array();
        
        foreach ($results as $result){
            if (!isset($components[$result['component']])){
                $components[$result['component']] = array();
            }
            if (array_search($result['action'], $components[$result['component']]) === FALSE){
                $components[$result['component']][] = $result['action'];
            }
            
        }
        
        return $components;
    }
}

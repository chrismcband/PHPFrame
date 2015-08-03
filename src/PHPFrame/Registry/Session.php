<?php
/**
 * PHPFrame/Registry/Session.php
 *
 * PHP version 5
 *
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Session.php 1025 2012-03-20 14:38:57Z chrismcband $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Session Class
 *
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Registry
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Registry_Session extends PHPFrame_Registry
{
	/**
	 * Instance of itself in order to implement the singleton pattern
	 *
	 * @var object of type PHPFrame_Registry_Session
	 */
	private static $_instance=null;
	/**
	 * A string used to name the session
	 *
	 * @var string
	 */
	private $_session_name="PHPFrame003";
	/**
	 * Cookie lifetime
	 *
	 * The time the cookie expires. This is a Unix timestamp so is in number of
	 * seconds since the epoch.
	 *
	 * @var int
	 */
	private $_cookie_lifetime=0;
	/**
	 * The path on the server in which the cookie will be available on. If set
	 * to '/', the cookie will be available within the entire domain . If set
	 * to '/foo/', the cookie will only be available within the /foo/ directory
	 * and all sub-directories such as /foo/bar/ of domain .
	 *
	 * @var string
	 */
	private $_cookie_path="/";
	/**
	 * The domain that the cookie is available. To make the cookie available on
	 * all subdomains of example.com then you'd set it to '.example.com'.
	 * The . is not required but makes it compatible with more browsers.
	 *
	 * @var string
	 */
	private $_cookie_domain=null;
	/**
	 * Indicates that the cookie should only be transmitted over a secure HTTPS
	 * connection from the client. When set to TRUE, the cookie will only be set
	 * if a secure connection exists.
	 *
	 * @var bool
	 */
	private $_cookie_secure=false;
	/**
	 * When TRUE the cookie will be made accessible only through the HTTP protocol.
	 * This means that the cookie won't be accessible by scripting languages, such
	 * as JavaScript. This setting can effectively help to reduce identity theft
	 * through XSS attacks (although it is not supported by all browsers).
	 *
	 * @var bool
	 */
	private $_cookie_httponly=true;
    /**
     * A prefix applied to all get and set session variables. Used to ensure
     * sessions shared across domains can have unique session variables.
     * 
     * @var string
     */
    private $_prefix='';
	
    private $_benchmark=false;
	
	/**
	 * Constructor
	 *
	 * @access protected
	 * @return void
	 * @since  1.0
	 */
	protected function __construct()
	{
		// Get path and domain to use for cookie
		$uri = new PHPFrame_Utils_URI(PHPFrame::Config()->get("BASE_URL"));
//		$this->_cookie_path = $uri->getDirname()."/";
//		$this->_cookie_domain = $uri->getHost();
//PHPFrame_Debug_Logger::write("COOKIE DOMAIN: ".$this->_cookie_domain);
// HACK TO SET COOKIE DOMAIN
		$this->_cookie_domain = ".stickyworld.com";
//PHPFrame_Debug_Logger::write("COOKIE DOMAIN: ".$this->_cookie_domain);
		
		// Set custom session name
		ini_set("session.name", $this->_session_name);
		
		// Initialise cookie
		ini_set("session.cookie_domain", $this->_cookie_domain);
		ini_set("session.cookie_lifetime", $this->_cookie_lifetime);
		ini_set("session.cookie_path", $this->_cookie_path);
		ini_set("session.cookie_secure", $this->_cookie_secure);
		ini_set("session.cookie_httponly", $this->_cookie_httponly);
		
		// start php session
		session_start();

		// If new session we initialise
		if (!isset($_SESSION['id']) || $_SESSION['id'] != session_id()) {
			// Store session id in session array
			$_SESSION['id'] = session_id();
			
			// Acquire session user object
			$_SESSION['user'] = new PHPFrame_User();
			$_SESSION['user']->set("id", 0);
			$_SESSION['user']->set("groupid", 0);
			
			// Acquire sysevents object
			$_SESSION['sysevents'] = new PHPFrame_Application_Sysevents();
			
			// Generate session token
			$this->getToken(true);
//			PHPFrame_Debug_Logger::write("Session: No session id set or session id does not match, detecting client");
			// Detect client for this session
			$this->detectClient();
			
		} elseif (
    		isset($_SERVER["HTTP_X_API_USERNAME"])
    		&& isset($_SERVER["HTTP_X_API_SIGNATURE"])
    		&& !($_SESSION['client'] instanceof PHPFrame_Client_XMLRPC)
		) {
			// If we are dealing with an api request that already has an existing session
			// but the client object is not set to XMLRPC we instantiate a new client object
			// replace it in the session, store the old one in another var as well as the 
			// user object so that we can put them back in place when the next non-api
			// request is received
			$_SESSION['overriden_client'] = $_SESSION['client'];
			$_SESSION['overriden_user'] = $_SESSION['user'];
			$_SESSION['client'] = new PHPFrame_Client_XMLRPC();
//			PHPFrame_Debug_Logger::write("Session: xmlrpc headers are provided, switching session to xmlrpc client");
			
		} elseif (
    		!isset($_SERVER["HTTP_X_API_USERNAME"])
    		&& !isset($_SERVER["HTTP_X_API_SIGNATURE"])
    		&& isset($_SESSION['overriden_client']) 
    		&& $_SESSION['overriden_client'] instanceof PHPFrame_Client_IClient
    		&& !($_SESSION['overriden_client'] instanceof PHPFrame_Client_XMLRPC)
		) {
//		    PHPFrame_Debug_Logger::write("Session: no xmlrpc headers detected, switching back to overriden_client, which should be default client");
		    // If we already have a session with an xmlrpc client object but no api
		    // headers are included in request we then revert the client and user objects
			$_SESSION['client'] = $_SESSION['overriden_client'];
            $_SESSION['user'] = $_SESSION['overriden_user'];
			unset($_SESSION['overriden_client']);
			unset($_SESSION['overriden_user']);
		} elseif (
		    !isset($_SERVER["HTTP_X_API_USERNAME"]) 
		    && !isset($_SERVER["HTTP_X_API_SIGNATURE"])
		    && ($_SESSION['client'] instanceof PHPFrame_Client_XMLRPC)
		) {
		    $_SESSION['overriden_client'] = $_SESSION['client'];
			$_SESSION['overriden_user'] = $_SESSION['user'];
			
			// Acquire session user object
			$_SESSION['user'] = new PHPFrame_User();
			$_SESSION['user']->set("id", 0);
			$_SESSION['user']->set("groupid", 0);
			
//		    PHPFrame_Debug_Logger::write("Session: client wrongly set to xmlrpc, setting overriden_client and detecting new client");
			$this->detectClient();
		}

        //set session prefix used for all subsequent session variable names
        $this->setPrefix(PHPFrame::Config()->get("OPENFIRE_USER_PREFIX").'_');

        //bench
        $this->set('benchmark',PHPFrame::Config()->get("BENCHMARK")||false);
	}

	/**
	 * Get Instance
	 *
	 * @static
	 * @access public
	 * @return PHPFrame_Registry
	 * @since  1.0
	 */
	public static function getInstance()
	{
		if (!isset(self::$_instance)) {
			self::$_instance = new self;
//PHPFrame_Debug_Logger::write("Session: ".print_r(self::$_instance,true));
//PHPFrame_Debug_Logger::write("User: ".print_r($_SESSION['user'],true));
		}
//PHPFrame_Debug_Logger::write("SESSION ID: ".self::$_instance->getId());
		return self::$_instance;
	}

	/**
	 * Get a session variable
	 *
	 * @param string $key           A string used to identify the session
	 *                              variable we want to retrieve.
	 * @param mixed  $default_value An optional default value to assign if
	 *                              the given key is not set.
	 *
	 * @access public
	 * @return mixed
	 * @since  1.0
	 */
	public function get($key, $default_value=null)
	{
        $key = $this->_prefix.$key;
		// Set default value if applicable
		if (!isset($_SESSION[$key]) && !is_null($default_value)) {
			$_SESSION[$key] = $default_value;
		}

		// If key is not set in session super global we return null
		if (!isset($_SESSION[$key])) {
			return null;
		}

		return $_SESSION[$key];
	}

	/**
	 * Set a session variable
	 *
	 * @param string $key   A string used to identify the session variable we
	 *                      want to set.
	 * @param mixed  $value The value we want to store in the specified key.
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function set($key, $value)
	{
        $key = $this->_prefix.$key;
		$_SESSION[$key] = $value;
	}

	/**
	 * Get session id
	 *
	 * @access public
	 * @return string
	 * @since  1.0
	 */
	public function getId()
	{
		return $_SESSION['id'];
	}

	/**
	 * Get session name
	 *
	 * @access public
	 * @return string
	 * @since  1.0
	 */
	public function getName()
	{
		return $this->_session_name;
	}

	/**
	 * Get client object
	 *
	 * @access public
	 * @return PHPFrame_Environment_IClient|null
	 * @since  1.0
	 */
	public static function getClient()
	{
		if (
		    isset($_SESSION['client'])
		    && $_SESSION['client'] instanceof PHPFrame_Client_IClient
		) {
			return $_SESSION['client'];
		}

		return null;
	}


	/**
	 * Get client object's name
	 *
	 * @access public
	 * @return string
	 * @since  1.0
	 */
	public function getClientName()
	{
		if (
		    isset($_SESSION['client'])
		    && $_SESSION['client'] instanceof PHPFrame_Client_IClient
		) {
			return $_SESSION['client']->getName();
		}

		return null;
	}

	/**
	 * Set session user
	 *
	 * @param PHPFrame_User $user User object of type PHPFrame_User
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function setUser(PHPFrame_User $user)
	{
		$_SESSION['user'] = $user;
	}

	/**
	 * Get session's user object
	 *
	 * @access public
	 * @return PHPFrame_User|null
	 * @since  1.0
	 */
	public function getUser()
	{
		if (
		    isset($_SESSION['user'])
		    && $_SESSION['user'] instanceof PHPFrame_User
		) {
			return $_SESSION['user'];
		}

		return null;
	}

	/**
	 * Get session user id
	 *
	 * @access public
	 * @return int
	 * @since  1.0
	 */
	public function getUserId()
	{
		if (
		    isset($_SESSION['user'])
		    && $_SESSION['user'] instanceof PHPFrame_User
		) {
			return (int) $_SESSION['user']->get('id');
		}

		return 0;
	}

	/**
	 * Get session user groupid
	 *
	 * @access public
	 * @return int
	 * @since  1.0
	 */
	public function getGroupId()
	{
		if (
		    isset($_SESSION['user'])
		    && $_SESSION['user'] instanceof PHPFrame_User
		) {
			return (int) $_SESSION['user']->get('groupid');
		}

		return 0;
	}

	/**
	 * Is the current session authenticated?
	 *
	 * @access public
	 * @return bool Returns TRUE if user is authenticated or FALSE otherwise.
	 * @since  1.0
	 */
	public function isAuth()
	{
		if (
		    isset($_SESSION['user'])
		    && $_SESSION['user'] instanceof PHPFrame_User
		    && $_SESSION['user']->get('id') > 0
		) {
			return true;
		}
		return false;
	}

	/**
	 * Is the current session an admin session?
	 *
	 * @access public
	 * @return bool Returns TRUE if current user is admin or FALSE otherwise.
	 * @since  1.0
	 */
	public function isAdmin()
	{
		if ($this->isAuth()) {
			return ($_SESSION['user']->get('groupid') == 1);
		}

		return false;
	}

	/**
	 * Get system events object
	 *
	 * @access public
	 * @return PHPFrame_Application_Sysevents
	 * @since  1.0
	 */
	public function getSysevents()
	{
		if (
		    isset($_SESSION['sysevents'])
		    && $_SESSION['sysevents'] instanceof PHPFrame_Application_Sysevents
		) {
			return $_SESSION['sysevents'];
		}

		return null;
	}

	/**
	 * Get a session token, if a token isn't set yet one will be generated.
	 *
	 * Tokens are used to secure forms from spamming attacks. Once a token
	 * has been generated the system will check the post request to see if
	 * it is present, if not it will invalidate the session.
	 *
	 * @param bool $force_new If true, force a new token to be created
	 *
	 * @access public
	 * @return string
	 * @since  1.0
	 */
	public function getToken($force_new=false)
	{
		//create a token
		if (!isset($_SESSION['token']) || $force_new) {
			$_SESSION['token'] = $this->_createToken(12);
		}

		return $_SESSION['token'];
	}

	/**
	 * Checks for a form token in the request
	 *
	 * @access public
	 * @return bool TRUE if found and valid, FALSE otherwise
	 * @since  1.0
	 */
	public function checkToken()
	{
		$request_token = PHPFrame::Request()->get($this->getToken(), '');

		if ($request_token == 1) {
			return true;
		} else {
			return false;
		}
	}

    /**
     * Sets a prefix that will be used for subsequent session variable
     * sets and gets. This is to be used when a session is being used
     * across different domains but session variables need to be
     * uniquely stored. If this is not specified session variables
     * will be stored and shared across all domains covered by the
     * cookie_domain.
     *
     * @param string $prefix
     * @return string
     */
    public function setPrefix($prefix) {
        $this->_prefix = $prefix;

        return $this->_prefix;
    }

	/**
	 * Destroy session
	 *
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function destroy()
	{
		// this destroys the session and generates a new session id
		session_regenerate_id(true);

		// Delete cookie. This has to be done using the same parameters
		// used when creating the cookie
		setcookie(
		    $this->_session_name,
            "", 
    		time() - 3600,
    		$this->_cookie_path,
    		$this->_cookie_domain,
    		$this->_cookie_secure,
    		$this->_cookie_httponly
    	);
	}
	
	/**
	 * Detect and set client object
	 *
	 * @access protected
	 * @return void
	 * @since  1.0
	 */
	protected function detectClient()
	{
		// Build array with available clients
		//TODO: This should be read from directory
		$available_clients = array("CLI", "Mobile", "Default");

		//loop through files
		foreach ($available_clients as $client) {
			//build class names
			$className = 'PHPFrame_Client_'.$client;
			if (is_callable(array($className, 'detect'))) {
				//call class's detect() to check if this is the helper we need
				$_SESSION['client'] = call_user_func(array($className, 'detect'));
				if ($_SESSION['client'] instanceof PHPFrame_Client_IClient) {
				    PHPFrame_Debug_Logger::write("Session: Detected client as ".$_SESSION['client']->getName());
					//break out of the function if we found our helper
					return;
				}
			}
		}

		//throw error if no helper is found
		throw new PHPFrame_Exception(_PHPFRAME_LANG_SESSION_ERROR_NO_CLIENT_DETECTED);
	}

	/**
	 * Create a token-string
	 *
	 *
	 * @param int $length Lenght of string.
	 *
	 * @access private
	 * @return string  Generated token.
	 * @since  1.0
	 */
	private function _createToken($length = 32)
	{
		static $chars = '0123456789abcdef';

		$max = strlen( $chars ) - 1;
		$token = '';
		$name = session_name();

		for($i=0; $i<$length; ++$i) {
			$token .= $chars[ (rand(0, $max)) ];
		}

		return md5($token.$name);
	}
}

<?php
/**
 * PHPFrame/User/OpenID.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage User
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * OpenID Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage User
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_User_OpenID
{
    # GetUserId(openid_url)
    //select user_id from user_openids where openid_url = openid_url
    
    # GetOpenIDsByUser(user_id)
    //select openid_url from user_openids where user_id = user_id
    
    # AttachOpenID(openid_url, user_id)
    //insert into user_openids values (openid_url, user_id)
    
    # DetachOpenID(openid_url, user_id)
    //delete from user_openids where openid_url = openid_url and user_id = user_id
    
    # DetachOpenIDsByUser(user_id)
    //delete from user_openids where user_id = user_id
}
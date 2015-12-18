<?php
/**
 * PHPFrame/Base/Number.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Number.php 73 2009-06-15 11:05:48Z luis.montero@e-noise.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Number Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Base_Number
{
    /**
     * Format bytes to human readable.
     * 
     * @param string $str The string we want to format.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public static function bytes($str) 
    {
        $unim = array("B","KB","MB","GB","TB","PB");
        $c = 0;
        while ($str>=1024) {
            $c++;
            $str = $str/1024;
        }
        return number_format($str,($c ? 2 : 0),",",".")." ".$unim[$c];
    }
}
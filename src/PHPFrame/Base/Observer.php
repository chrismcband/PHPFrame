<?php
/**
 * PHPFrame/Base/Observer.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */

/**
 * Observer class
 * 
 * This class provides an abstract implementation of the SplObserver interface.
 * 
 * This class is designed to work together with the PHPFrame_Base_Subject class.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Base_Subject
 * @since      1.0
 */
abstract class PHPFrame_Base_Observer implements SplObserver
{
    /**
     * Update 
     * 
     * @param PHPFrame_Base_Subject $subject Instance of the subject issuing the update
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function update(SplSubject $subject)
    {
        $this->doUpdate();
    }
    
    /**
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    abstract protected function doUpdate(SplSubject $subject);
}
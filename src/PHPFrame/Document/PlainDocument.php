<?php
/**
 * PHPFrame/Document/PlainDocument.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * XML Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_PlainDocument extends PHPFrame_Document
{
    /**
     * Constructor.
     * 
     * @param string $mime    [Optional] The document's MIME type. The default 
     *                        value is 'text/plain'.
     * @param string $charset [Optional] The document's character set. Default 
     *                        value is 'UTF-8'.
     * 
     * @return void
     * @since  1.0 
     */
    public function __construct($mime="text/plain", $charset=null) 
    {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);
    }
    
    /**
     * Convert object to string
     * 
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";
        
        $title = $this->getTitle();
        if (!empty($title)) {
            $str .= $title."\n";
            for ($i=0; $i<strlen($title); $i++) {
                $str .= "-";
            }
            $str .= "\n\n";
        }
        
        $sysevents = PHPFrame::getSession()->getSysevents();
        if (count($sysevents) > 0) {
            $str .= (string) $sysevents;
            $str .= "\n";
        }
        
        $body = $this->getBody();
        if (!empty($body)) {
            $str .= $body."\n";
        }
        
        return $str;
    }
}

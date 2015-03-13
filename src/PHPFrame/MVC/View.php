<?php
/**
 * PHPFrame/MVC/View.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage MVC
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: View.php 1009 2011-10-06 13:02:21Z iqabsent $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * View Class
 * 
 * This class is used to implement the MVC (Model/View/Controller) architecture 
 * in the components.
 * 
 * Views are used to render the output of a component into a form suitable for 
 * interaction, typically a user interface element. Multiple views can exist for 
 * a single component for different purposes.
 * 
 * This class should be extended when creating component views as it is an 
 * abstract class. This class is used as a template for creating views when 
 * developing components. See the built in components (dashboard, user, admin, ...) 
 * for examples.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage MVC
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_MVC_ActionController, PHPFrame_MVC_Model
 * @since      1.0
 * @abstract
 */
abstract class PHPFrame_MVC_View
{
    /**
     * The view name
     * 
     * @var string
     */
    protected $_name=null;
    /**
     * The layout to load. Typical values: "list", "detail", "form", ...
     * 
     * @var string
     */
    protected $_layout=null;
    /**
     * Data array for view
     * 
     * @var array
     */
    protected $_data=array();
    /**
     * A pathway object for this view
     * 
     * @var PHPFrame_Application_Pathway
     */
    protected $_pathway=null;
    /**
     * A reference to the document used to render this view
     * 
     * @var PHPFrame_Document
     */
    protected $_document=null;
    /**
     * The component which this view belongs to
     *
     * @var string
     */
    protected $_component=null;
    
    /**
     * Constructor
     * 
     * @param string $name   The view name
     * @param string $layout Optional parameter to specify a specific layout.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($name, $layout=null) 
    {
        $this->_name = (string) $name;
        $this->_layout = (string) $layout;
        
        // Acquire pathway object
        $this->_pathway = new PHPFrame_Application_Pathway();
        
        // Get reference to the document used to render the view
        // This document is stored in the response object
        $this->_document = PHPFrame::Response()->getDocument();
    }
    
    public function getName()
    {
        return $this->_name;
    }

    public function getComponentName()
    {
        return $this->_component;
    }

    public function setComponentName($component)
    {
        $this->_component = $component;
    }
    
    public function getLayout()
    {
        return $this->_layout;
    }
    
    /**
     * Add a variable to data array
     * 
     * @param string $key   The name of the variable inside the view.
     * @param mixed  $value The variable we want to add to the view.
     * 
     * @return void
     * @since  1.0
     */
    public function addData($key, $value) 
    {
        $this->_data[$key] = $value;
    }
    
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * Get the view's pathway object
     * 
     * @return PHPFrame_Application_Pathway
     */
    public function getPathway()
    {
        return $this->_pathway;
    }
    
    /**
     * Get the view's pathway object
     * 
     * @return PHPFrame_Application_Pathway
     */
    public function getDocument()
    {
        return $this->_document;
    }
    
    /**
     * Display the view
     * 
     * This method loads the template layer of the view.
     * 
     * This method  also trigger layout specific methods. 
     * For example, if we are displaying layout "list" and there is a method 
     * called displayMyviewList within the extended view class this method 
     * will be automatically invoked.
     * 
     * @return void
     * @since  1.0
     */
    public function display() 
    {
		//bench
		$bench = PHPFrame::Session()->get('benchmark', false);
		if($bench) {
			$start = PHPFrame::Session()->get('bench_start',0);
			if($start) {
				$finish = microtime(true);
				PHPFrame::Session()->set('bench_start',$finish);
				$component = PHPFrame::Request()->getComponentName();
				$action = PHPFrame::Request()->getAction();
		        PHPFrame_Debug_Logger::write("<<BENCH>> ACTION: $component/$action ".($finish-$start));
			}
		}
		
        // If there is a layout specific method we trigger it before rendering
        $layout_array = explode('_', $this->_layout);
        $layout = '';
        for ($i=0; $i<count($layout_array); $i++) {
            $layout .= ucfirst($layout_array[$i]);
        }
        $tmpl_specific_method = "display".ucfirst($this->_name).ucfirst($layout);
        if (method_exists($this, $tmpl_specific_method)) {
            // Invoke layout specific display method
            $this->$tmpl_specific_method();
        }
        
        // Delegate rendering to response object
        // The response object will render the view object 
        // depending on the document typ
        PHPFrame::Response()->render($this);
    }
}

<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	registry
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Abstract Registry Class
 * 
 * @package		PHPFrame
 * @subpackage 	registry
 * @since 		1.0
 */
abstract class PHPFrame_Registry 
{
	/**
	 * Constructor
	 * 
	 * @access	protected
	 * @return	void
	 * @since	1.0
	 */
	abstract protected function __construct();
	
	/**
	 * Get Instance
	 * 
	 * @static
	 * @access	public
	 * @return 	PHPFrame_Registry
	 * @since	1.0
	 */
	abstract public static function getInstance();
	
	/**
	 * Get a registry variable
	 * 
	 * @access	public
	 * @param	string	$key
	 * @param	mixed	$default_value
	 * @return	mixed
	 * @since	1.0
	 */
	abstract public function get($key, $default_value=null);
	
	/**
	 * Set a registry variable
	 * 
	 * @access	public
	 * @param	string	$key
	 * @param	mixed	$value
	 * @return	void
	 * @since	1.0
	 */
	abstract public function set($key, $value);
}

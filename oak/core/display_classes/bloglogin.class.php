<?php

/**
 * Project: Oak
 * File: bloglogin.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

// load the display interface
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'systemlogin.class.php');

class Display_BlogLogin extends Display_SystemLogin {
	
	/**
	 * Singleton
	 *
	 * @var object
	 */
	private static $instance = null;
	
	/**
	 * Reference to base class
	 *
	 * @var object
	 */
	public $base = null;
	
	/**
	 * Container for project information
	 * 
	 * @var array
	 */
	protected $_project_info = array();
	
	/**
	 * Container for page information
	 * 
	 * @var array
	 */
	protected $_page_info = array();
	
	/**
	 * Container for simple page information
	 * 
	 * @var array
	 */
	protected $_simple_page = array();
	
/**
 * Creates new instance of display driver. Takes an array
 * with the project information as first argument, an array
 * with the information about the current page as second
 * argument, the simple page content as third argument.
 * 
 * @param array Project information
 * @param array Page information
 * @param array Simple page content
 */
public function __construct($project_info, $page_info)
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
				
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
	
	// input check
	if (!is_array($project_info)) {
		throw new Display_BlogLoginException("Input for parameter project_info is expected to be an array");
	}
	if (!is_array($page_info)) {
		throw new Display_BlogLoginException("Input for parameter page_info is expected to be an array");
	}
	
	$this->_project_info = $project_info;
	$this->_page_info = $page_info;
}

/**
 * Loads new instance of display driver. See the constructor
 * for an argument description.
 *
 * In comparison to the constructor, it can be called using
 * call_user_func_array(). Please note that's not a singleton.
 * 
 * @param array Project information
 * @param array Page information
 * @param array Content info
 * @return object New display driver instance
 */
public static function instance($project_info, $page_info, $content_info = array())
{
	return new Display_BlogLogin($project_info, $page_info);
}

// end of class
}

class Display_BlogLoginException extends Exception { }

?>
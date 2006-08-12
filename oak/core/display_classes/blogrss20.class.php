<?php

/**
 * Project: Oak
 * File: blogrss20.class.php
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
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'display.interface.php');

class Display_BlogRss20 implements Display {
	
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
 * Creates new instance of display driver. Takes an array
 * with the project information as first argument, an array
 * with the information about the current page as second
 * argument, the simple page content as third argument.
 * 
 * @param array Project information
 * @param array Page information
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
		throw new Display_BlogRss20("Input for parameter project_info is expected to be an array");
	}
	if (!is_array($page_info)) {
		throw new Display_BlogRss20("Input for parameter page_info is expected to be an array");
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
 * @param array Page content
 * @return object New display driver instance
 */
public static function instance($project_info, $page_info, $content_info = array())
{
	return new Display_BlogRss20($project_info, $page_info);
}

/**
 * Default method that will be called from the display script
 * and has to care about the page preparation. Returns boolean
 * true on success.
 * 
 * @return bool
 */ 
public function render ()
{
	// nothing to do
	return true;
}

/**
 * Returns the cache mode for the current template.
 * 
 * @return int
 */
public function getMainTemplateCacheMode ()
{
	return 0;
}

/**
 * Returns the cache lifetime of the current template.
 * 
 * @return int
 */
public function getMainTemplateCacheLifetime ()
{
	return 0;
}

/** 
 * Returns the name of the current template.
 * 
 * @return string
 */ 
public function getMainTemplateName ()
{
	return "oak:blog_rss20.".OAK_CURRENT_PAGE;
}

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems) with the
 * Location: header prepended.
 * 
 * @return string
 */
public function getRedirectLocationSelf ()
{
	return "Location: ".$this->getLocationSelf();
}

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems).
 * 
 * @return string
 */
public function getLocationSelf ()
{
	if ($this->_page_info['index_page']) {
		return 'index.php';
	} else {
		return sprintf("index.php?page=%u&action=Rss20", $this->_page_info['id']);
	}
}

/**
 * Returns information whether to skip authentication
 * or not.
 * 
 * @return bool
 */
public function skipAuthentication ()
{
	return false;
}

// end of class
}

class Display_BlogRss20Exception extends Exception { }

?>
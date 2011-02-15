<?php

/**
 * Project: Welcompose
 * File: base.class.php
 * 
 * Copyright (c) 2008 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id: base.class.php 50 2007-02-17 21:49:16Z andreas $
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Application_PingService.
 * 
 * @return object
 */
function Base_Base ()
{
	if (Base_Base::$instance == null) {
		Base_Base::$instance = new Base_Base(); 
	}
	return Base_Base::$instance;
}

class Base_Base {

	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
	/**
	 * Reference to Base_Utility class
	 *
	 * @var object
	 */
	public $utility = null;
	
	/**
	 * Reference to error handling class
	 *
	 * @var object
	 */
	public $error = null;

	/**
	 * Reference to database class
	 *
	 * @var obejct
	 */
	public $db = null;
	
	/** 
	 * debug mode setting
	 *
	 * @var bool
	 */
	protected $_debug = false;
	
	/**
	 * container for configuration settings
	 *
	 * @var array
	 */
	public $_conf = array();
	
	/**
	 * container for default values
	 * 
	 * @var array
	 */
	public $_defaults = array();

public function __construct ()
{
	// load Base_Compat
	require_once('compat.class.php');
	
	// set new include paths
	$this->setIncludePath();
	
	// load configuration
	$this->loadConfiguration();
	
	// load error class
	$this->loadClass('error');
	
	// load cnc class
	$this->loadClass('cnc');
	
	// load constants
	$this->loadConstants();
	
	// load defaults
	$this->loadDefaults();
	
	// configure locales
	$this->configureLocales();
	
	
	// load utility class
	$this->loadClass('utility');
}

/**
 * Singleton
 * 
 * Returns instance of the base object.
 *
 * @return object
 */
public static function instance()
{ 
	if (Base_Base::$instance == NULL) {
		Base_Base::$instance = new Base_Base(); 
	}
	return Base_Base::$instance;
} 

/**
 * Set include path
 * 
 * Configures new include paths. Defines the following
 * paths:
 * 
 * <ul>
 * <li>Same directory (.)</li>
 * <li>Parent directory (../)</li>
 * <li>PEAR directory (../pear)</li>
 * </ul>
 * 
 * The function will only be executed when base::_use_ini_set
 * is true.
 */
protected function setIncludePath ()
{
	$parts = pathinfo(__FILE__);
	
	// define all the paths to use
	$paths[] = '.';
	$paths[] = $parts['dirname'];
	$paths[] = realpath(sprintf('%s/../pear', $parts['dirname']));
	
	if (set_include_path(implode(PATH_SEPARATOR, $paths)) === false) {
		trigger_error('Unable to set new include path', E_USER_ERROR);
	}
}

/**
 * Load constants
 * 
 * Loads files with constants (regex etc.).
 *
 * @return bool
 */
protected function loadConstants ()
{
	// load regex constants
	$file = dirname(__FILE__).'/../conf/regex.inc.php';
	$file = Base_Compat::fixDirectorySeparator($file);
	if (file_exists($file) && is_readable($file)) {
		require_once($file);
	} else {
		trigger_user_error(BASE_ERROR_NOTICE, 'Constant file regex.inc.php not found', __FILE__, __LINE__);
	}
	
	return true;
}

/**
 * Load configuration
 * 
 * Loads configuration information from sys.inc.php
 * and attaches the contents to BASE::_conf. It also
 * takes care of configuring the settings for debug
 * and development modes.
 *
 * @return bool
 */
protected function loadConfiguration ()
{
	// prepare path to sys.inc.php
	$file = Base_Compat::fixDirectorySeparator(dirname(__FILE__).'/../conf/sys.inc.php');
	
	// check if the config file exists. if it doesn't exist,
	// exit from the function.
	if (!file_exists($file) || !is_readable($file)) {
		trigger_error('Configuration file sys.inc.php not useable', E_USER_ERROR);
	}
		
	// assign the contents to BASE::_conf
	$this->_conf = parse_ini_file($file, true);
	
	// enable/disable debug mode
	if (isset($this->_conf['environment']['debug'])) {
		$this->_debug = (bool)$this->_conf['environment']['debug'];
	}
	
	return true;
}

/**
 * Load defaults
 * 
 * Load default settings from defaults.inc.php and
 * attaches them to BASE::_defaults.
 *
 * @return bool
 */
protected function loadDefaults ()
{
	// prepare path to defaults.inc.php
	$file = Base_Compat::fixDirectorySeparator(dirname(__FILE__).'/../conf/defaults.inc.php');
	
	// check if the defaults.inc.php exists
	if (!file_exists($file) || !is_readable($file)) {
		trigger_user_error(BASE_ERROR_NOTICE, 'Configuration file defaults.inc.php not useable', __FILE__, __LINE__);
		return false;
	}
	
	// parse defaults
	$this->_defaults = parse_ini_file($file, true);
	
	return true;
}

/**
 * Load class
 * 
 * Loads requested class. Takes the class name as
 * first argument. Supported class names:
 * 
 * <ul>
 * <li>cnc</li>
 * <li>database</li>
 * <li>error</li>
 * <li>utility</li>
 * </ul>
 * 
 * @param string Class name
 */
public function loadClass ($class)
{
	switch (strtolower($class)) {
		case 'cnc':
				// load requested file
				$file = dirname(__FILE__).'/cnc.class.php';
				$file = Base_Compat::fixDirectorySeparator($file);
				
				// load class
				if (!($this->utility instanceof Base_Cnc)) {
					require($file);
					$this->utility = new Base_Cnc();
				}
			break;
		case 'database':
				// make sure a database driver is configured
				if (!isset($this->_conf['database']['driver'])) {
					trigger_error('No database driver configured', E_USER_ERROR);
				}
				
				// load class
				if (!class_exists('Base_Database') || ($this->db instanceof Base_Database) === false) {
					switch ($this->_conf['database']['driver']) {
						case 'mysqli':
								// load requested database driver
								$file = dirname(__FILE__).'/mysqli.class.php';
								$file = Base_Compat::fixDirectorySeparator($file);
								require($file);
								
								// create new instance of Base_Database
								$this->db = new Base_Database($this->_conf['database']);
							break;
						case 'pdo':
								// load requested database driver
								$file = dirname(__FILE__).'/pdo.class.php';
								$file = Base_Compat::fixDirectorySeparator($file);
								require($file);
								
								// create new instance of Base_Database
								$this->db = new Base_Database($this->_conf['database']);
							break;
						default:
							trigger_error('Unknown database driver configured', E_USER_ERROR);
					}
				}
			break;
		case 'error':
				// load requested file
				$file = dirname(__FILE__).'/error.class.php';
				$file = Base_Compat::fixDirectorySeparator($file);
				require_once($file);
				
				// load BASE_Error
				$this->error = Base_Error($this->_conf['log']['handler'], $this->_conf['log']['name'],
					$this->_conf['log']['level']);
				
				// enable custom error handler
				set_error_handler(array($this->error, 'phpErrorHandler'));
			break;
		case 'utility':
				// load requested file
				$file = dirname(__FILE__).'/utility.class.php';
				$file = Base_Compat::fixDirectorySeparator($file);
				
				// load class
				if (!($this->utility instanceof Base_Utility)) {
					require($file);
					$this->utility = new Base_Utility();
				}
			break;
		default:
			trigger_error("Unable to load unknown class $class", E_USER_ERROR);
	}
}

/**
 * Configure locales
 * 
 * Configures locales according to the settings in
 * BASE::_conf['locales']. It loops through
 * BASE::_conf['locales'] and looks for the following
 * config keys:
 * 
 * <ul>
 * <li>all: Setting for LC_ALL</li>
 * <li>collate: Setting for LC_COLLATE</li>
 * <li>cytpe: Setting for LC_CTYPE</li>
 * <li>monetary: Setting for LC_MONETARY</li>
 * <li>numeric: Setting for LC_NUMERIC</li>
 * <li>time: Setting for LC_TIME</li>
 * </ul>
 *
 * @return bool
 */
protected function configureLocales ()
{
	// check if the base::_conf['loacles'] is set and an array
	if (!isset($this->_conf['locales']) || !is_array($this->_conf['locales'])) {
		trigger_user_error(BASE_ERROR_NOTICE, 'Locales not configured, using default values', __FILE__, __LINE__);
		return false;
	}
	
	// set new locales
	foreach ($this->_conf['locales'] as $_locale => $_value) {
		switch ($_locale) {
			case 'all':
					if (setlocale(LC_ALL, $_value) === false) {
						trigger_user_error(BASE_ERROR_NOTICE, "The configured locale for LC_ALL is not supported",
							__FILE__, __LINE__);
					}
				break;
			case 'collate':
					if (setlocale(LC_COLLATE, $_value) === false) {
						trigger_user_error(BASE_ERROR_NOTICE, "The configured locale for LC_COLLATE is not supported",
							__FILE__, __LINE__);
					}
				break;
			case 'ctype':
					if (setlocale(LC_CTYPE, $_value) === false) {
						trigger_user_error(BASE_ERROR_NOTICE, "The configured locale for LC_CTYPE is not supported",
							__FILE__, __LINE__);
					}
				break;
			case 'monetary':
					if (setlocale(LC_MONETARY, $_value) === false) {
						trigger_user_error(BASE_ERROR_NOTICE, "The configured locale for LC_MONETARY is not supported",
							__FILE__, __LINE__);
					}
				break;
			case 'numeric':
					if (setlocale(LC_NUMERIC, $_value) === false) {
						trigger_user_error(BASE_ERROR_NOTICE, "The configured locale for LC_NUMERIC is not supported",
							__FILE__, __LINE__);
					}
				break;
			case 'time':
					if (setlocale(LC_TIME, $_value) === false) {
						trigger_user_error(BASE_ERROR_NOTICE, "The configured locale for LC_TIME is not supported",
							__FILE__, __LINE__);
					}
				break;
			default:
					trigger_user_error(BASE_ERROR_NOTICE, "Configured locale type $_locale is unknown",
						__FILE__, __LINE__);
				break;
		}
	}
	
	return true;
}

/**
 * Debug enabled
 * 
 * Read only interface to the protected property
 * BASE::_debug. Returns whether the debug mode
 * is enabled or off.
 *
 * @return bool
 */
public function debug_enabled ()
{
	if ($this->_debug === true) {
		return true;
	} else {
		return false;
	}
}

/**
 * Reconfigure Locales
 * 
 * Interface to BASE::configureLocales();
 *
 * @return bool
 */
public function reconfigureLocales ()
{
	return $this->configureLocales();
}

/**
 * Public interface to Base_Base::loadConfiguration();
 * 
 * @return bool
 */
public function reloadConfiguration ()
{
	return $this->loadConfiguration();
}

// End of class
}

?>
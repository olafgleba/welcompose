<?php

/**
 * Project: Welcompose
 * File: chmod.class.php
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
 * $Id$
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton. Returns instance of the Utility_Chmod object.
 * 
 * @return object
 */
function Utility_Chmod ()
{ 
	if (Utility_Chmod::$instance == null) {
		Utility_Chmod::$instance = new Utility_Chmod(); 
	}
	return Utility_Chmod::$instance;
}

class Utility_Chmod {
	
	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
	/**
	 * Reference to base class
	 * 
	 * @var object
	 */
	public $base = null;
	
	/**
	 * Default chmod value that will be applied to files.
	 *
	 * @param int
	 */
	public $_default_file_mask = 0644;
	
	/**
	 * Default chmod value that will be applied to directories.
	 *
	 * @param int
	 */
	public $_default_dir_mask = 0755;
	
	/**
	 * Chmod value that will be applied to files that are supposed
	 * to be writable.
	 * 
	 * @param int
	 */
	public $_writable_file_mask = 0666;
	
	/**
	 * Chmod value that will be applied to directories that are
	 * supposed to be writable.
	 * 
	 * @param int
	 */
	public $_writable_dir_mask = 0777;
	
	/**
	 * List of files that are supposed to be writable
	 *
	 * @param array
	 */
	public $_writable_files = array(
		
	);
	
	/**
	 * List of directories that are supposed to be writable
	 *
	 * @param array
	 */
	public $_writable_dirs = array(
		'/'
	);

	
/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
public function __construct()
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
}

/**
 * Sets default chmod mode for files. Please note that you
 * have to supply a four digit long octal mode value.
 *
 * @throws Utility_ChmodException
 * @param int 
 */
public function setDefaultFileMask ($mode)
{
	// input check
	if (!is_numeric($mode) || strlen($mode) != 4) {
		throw new Utility_ChmodException("Invalid mode supplied");
	}
	
	$this->_default_file_mask = (int)$mode;
}

/**
 * Sets default chmod mode for directories. Please note that you
 * have to supply a four digit long octal mode value.
 *
 * @throws Utility_ChmodException
 * @param int 
 */
public function setDefaultDirMask ($mode)
{
	// input check
	if (!is_numeric($mode) || strlen($mode) != 4) {
		throw new Utility_ChmodException("Invalid mode supplied");
	}
	
	$this->_default_dir_mask = (int)$mode;
}

/**
 * Sets chmod mode for files that are supposed to be writable.
 * Please note that you have to supply a four digit long octal
 * mode value.
 *
 * @throws Utility_ChmodException
 * @param int 
 */
public function setWritableFileMask ($mode)
{
	// input check
	if (!is_numeric($mode) || strlen($mode) != 4) {
		throw new Utility_ChmodException("Invalid mode supplied");
	}
	
	$this->_writable_file_mask = (int)$mode;
}

/**
 * Sets chmod mode for directories that are supposed to be writable.
 * Please note that you have to supply a four digit long octal
 * mode value.
 *
 * @throws Utility_ChmodException
 * @param int 
 */
public function setWritableDirMask ($mode)
{
	// input check
	if (!is_numeric($mode) || strlen($mode) != 4) {
		throw new Utility_ChmodException("Invalid mode supplied");
	}
	
	$this->_writable_dir_mask = (int)$mode;
}

/**
 * Changes rights to files to fit the writable file mask definition.
 *
 * @throws Utility_ChmodException
 * @param string
 */
public function chmodFileWritable ($file)
{	
	if (@chmod($file, $this->_writable_file_mask) === false) {
		throw new Utility_ChmodException("Failed to apply writable rights to: $file");
	}
}

/**
 * Changes rights to files to fit the default file mask definition.
 *
 * @throws Utility_ChmodException
 * @param string
 */
public function chmodFileDefault ($file)
{	
	if (@chmod($file, $this->_default_file_mask) === false) {
		throw new Utility_ChmodException("Failed to apply default rights to: $file");
	}
}

/**
 * Changes rights to dir to fit the writable dir mask definition.
 *
 * @throws Utility_ChmodException
 * @param string
 */
public function chmodDirWritable ($dir)
{	
	if (@chmod($dir, $this->_writable_dir_mask) === false) {
		throw new Utility_ChmodException("Failed to apply writable rights to: $dir");
	}
}

/**
 * Changes rights to dir to fit the default dir mask definition.
 *
 * @throws Utility_ChmodException
 * @param string
 */
public function chmodDirDefault ($dir)
{	
	if (@chmod($dir, $this->_default_dir_mask) === false) {
		throw new Utility_ChmodException("Failed to apply default rights to: $dir");
	}
}

// end of class
}

class Utility_ChmodException extends Exception { }

?>
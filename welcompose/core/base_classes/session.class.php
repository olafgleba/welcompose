<?php

/**
 * Project: Welcompose
 * File: session.class.php
 * 
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * $Id: session.class.php 48 2007-01-19 15:49:28Z andreas $
 * 
 ** 
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Base_Session.
 * 
 * @return object
 */
function Base_Session ()
{
	if (Base_Session::$instance == null) {
		Base_Session::$instance = new Base_Session(); 
	}
	return Base_Session::$instance;
}

class Base_Session {

	/**
	*Singleton
	*@var object
	*/
	public static $instance = null;

	/**
	*Wheter there's an open session or not
	*@var bool
	*/
	protected $_session_started = false;
	
	/**
	*Regex for the session name
	*@var string
	*/
	protected  $_regex_session_name = '=^([a-z0-9-_]+)$=i';
	
	/**
	*Regex for the session id
	*@var string
	*/
	protected $_regex_session_id = '=^[a-z0-9-,]{22,40}$=i';

/**
*Start session
*
*Creates new session object.
*
*<b>Example 1: Create new session object</b>
*
*<code>
*$session = session::instance();
*</code>
*/
public function __construct ()
{
	// load cnc class
	require_once(dirname(__FILE__).'/cnc.class.php');
	
	// start the session
	$this->open();
}

/**
*Open New Sessions
*
*If there's no open session, the method creates
*a new one.
*/
public function open ()
{
	// if the session wasn't started before,
	// start it and set session::_session_started
	// to true.
	if ($this->isStarted() !== true) {
		session_start();
		$this->_session_started = true;
	}
}

/**
*Save Session
*
*Saves the current session (e.g. for redirect).
*
*@return bool
*/
public function save ()
{
	if ($this->isStarted() === true) {	
		// set session::_session_started to false
		$this->_session_started = false;
		
		// close the session
		session_write_close();
	}
	
	return true;
}

/**
*Clear Session
*
*Removes all the data from the session.
*
*@return bool
*/
public function clear ()
{
	if ($this->isStarted() === true) {
		if (is_array($_SESSION)) {
			// if $_SESSION is an array, loop through and unset
			// every key
			foreach ($_SESSION as $_key => $_value) {
				unset($_SESSION[$key]);	
			}
		} else {
			$_SESSION = null;	
		}
		
		$_SESSION = array();
	}

	return true;
}

/**
*Close Session
*
*Method to kill the session. Removes the session and
*all the data.
*
*@return bool
*/
public function close ()
{
	if ($this->isStarted() === true) {
		// get the session name
		$session_name = $this->getName();
		
		// if the cookie with the current session name exists,
		// replace it by another one that's already expired
		if (isset($_COOKIE[$session_name])) {
			setcookie($session_name, '', time() - 604800, '/');
		}
		
		// empty the super global
		$this->clear();
	}
	
	return true;
}

/**
*Get Name
*
*Returns the session name.
*
*@throws Base_SessionException
*@return string
*/
public function getName ()
{
	$name = session_name();

	if (is_null(Base_Cnc::filterRequest($name, $this->_regex_session_name))) {
		throw new Base_SessionException("New session name does not match session naming convention");	
	}
	
	return $name;
}

/**
*Set Name
*
*Method to set a new session name. Takes the new
*session name as first argument. Returns the
*session name.
*
*@throws Base_SessionException
*@param string Session name
*@return string
*/
public function setName ($name)
{
	// input check
	if (is_null(Base_Cnc::filterRequest($name, $this->_regex_session_name))) {
		throw new Base_SessionException("New session name does not match session naming convention");	
	}

	// set session name
	return session_name($name);
}

/**
*Get Id
*
*Returns the current session id.
*
*@throws Base_SessionException
*@return string
*/
public function getId ()
{
	// get session id
	$id = session_id();
	
	// check session id
	if (is_null(Base_Cnc::filterRequest($id, $this->_regex_session_id))) {
		throw new Base_SessionException("Session id does not match naming convention");
	} else {
		return $id;	
	}
}

/**
*Set Id
*
*Method to set a new session id. Takes the new session
*id as first argument. Returns the session id.
*
*@throws Base_SessionException
*@param string Session id
*@return string
*/
public function setId ($id)
{
	// input check
	if (is_null(Base_Cnc::filterRequest($id, $this->_regex_session_id))) {
		throw new Base_SessionException("Session id does not match naming convention");
	}
	
	// set new session id
	return session_id($id);
}

/**
*Regenerate Id
*
*Replaces the current session id with a new one
*and removes the old session data.
*
*@return bool
*/
public function regenerateId ()
{
	// session_regenerate_id doesn't remove the old session
	// file the in PHP 5.0.x and earlier. This has been fixed
	// in PHP 5.1.0. So for versions before 5.1.0 we need to
	// do it for ourselve.
	if (version_compare(PHP_VERSION, '5.1.0', '<')) {
		// get the session id
		$old_id = $this->getId();

		// regenerate session id
		session_regenerate_id();
		
		// get session_save_path
		$session_save_path = session_save_path();
		if (empty($session_save_path)) {
			$session_save_path = '/tmp';	
		}
		if (strpos($session_save_path, ";") !== FALSE) {
			$session_save_path = substr($session_save_path, strpos($session_save_path, ";") + 1);
		}
		
		// remove the trailing slash from the session_save_path
		$session_save_path = Base_Cnc::removeTrailingSlash($session_save_path);
		
		// compose the path to the session file
		$path_to_session = sprintf("%s/sess_%s", $session_save_path, $old_id);
		
		// remove the session
		@unlink($path_to_session);
	} else {
		session_regenerate_id(true);	
	}
	
	return true;
}

/**
*Is started
*
*Method to see if there's an open session or not.
*
*@return bool
*/
public function isStarted ()
{
	if ((bool)$this->_session_started === true) {
		return true;	
	} else {
		return false;	
	}
}

// end of class
}

class Base_SessionException extends Exception {}

?>
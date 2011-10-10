<?php

/**
 * Project: Welcompose
 * File: cookie.class.php
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
 * $Id: cookie.class.php 48 2007-01-19 15:49:28Z andreas $
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

class Base_Cookie {

	/**
	 * PEAR::Crypt_RC4 object
	 * @var object
	 */
	protected $rc4 = null;
	
	/**
	 * Secret to be used to encrypt the cookie contents
	 * @var string
	 */
	protected $_app_key = null;
	
	/**
	 * Name of the cookie
	 * @var string
	 */
	protected $_cookie_name = null;
	
	/**
	 * Data to be saved in the cookie
	 * @var mixed
	 */
	protected $_data = null;
	
	/**
	 * Expiration date of the cookie (offset to gmmktime())
	 * @var int
	 */
	protected $_expiration = null;
	
	/**
	 * Cookie path
	 * @var string
	 */
	protected $_path = "/";
	
	/**
	 * How to react when cookie is tainted. If set to true,
	 * getCookieData() will simply return null. If set to false,
	 * an exception will be thrown.
	 * @var bool
	 */
	protected $_taint_silent = true;
	
/**
 * Creates new cookie object. Takes application key to be used to encrypt
 * the cookie contents as first argument, the cookie name as second
 * argument. 
 *
 * @throws Base_CookieException
 * @param string Application key
 * @param string Cookie name
 */
public function __construct($app_key, $cookie_name)
{
	// load cnc class
	require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'cnc.class.php');
	
	// load PEAR::Crypt_RC4
	require_once('Crypt/Rc4.php');
	
	// input check
	if (empty($app_key) || !is_scalar($app_key)) {
		throw new Base_CookieException("No useable application key specified");
	}
	if (empty($cookie_name) || !is_scalar($cookie_name)) {
		throw new Base_CookieException("No useable cookie name specified");
	}
	
	$this->_app_key = $app_key;
	$this->_cookie_name = $cookie_name;
	
	// create instance of PEAR::Crypt_RC4
	$this->rc4 = new Crypt_RC4($this->_app_key);
	
	// get cookie data
	$this->readCookieData();
	
	// get base instance
	$this->base = load('base:base');
	
	// Expiration date of the cookie (offset to gmmktime())
	$this->_expiration = $this->base->_conf['cookie']['lifetime'];
}

/** 
 * Sets cookie data.
 *
 * @param mixed 
 */
public function setCookieData ($data)
{
	$this->_data = $data;
}

/**
 * Sets cookie using the data structure defined in Base_Cookie::data.
 * 
 * @return bool
 */
public function setCookie ()
{
	return $this->writeCookieData();
}

/**
 * Returns cookie data. If Base_Cookie::setCookieData() has been called
 * before, you'll get the data created by the last Base_Cookie::setCookieData()
 * call. To retrieve the original cookie data, use
 * Base_Cookie::getInitialCookieData().
 *
 * @return mixed
 */
public function getCookieData ()
{
	return $this->_data;
}

/**
 * Returns initial cookie data (from startup).
 *
 * @return mixed
 */ 
public function getInitialCookieData ()
{
	return $this->_initial_data;
}

/**
 * Writes data to cookie, or to be correct, to two cookies. One cookie will
 * be filled with the serialized, encrypted data and the second one (name
 * prefixed with "_fingerprint") will be filled with a fingerprint to verify
 * the encrypted data. Returns bool.
 *
 * @throws Base_CookieException  
 * @return bool
 */
protected function writeCookieData ()
{
	// make sure that there is some app key for encryption
	if (empty($this->_app_key) || !is_scalar($this->_app_key)) {
		throw new Base_CookieException("No useable application key specified");
	}
	
	// use current app key
	$this->rc4->setKey($this->_app_key);
	
	// serialize cookie data
	$data = serialize($this->_data);
	
	// create fingerprint
	$fingerprint = sha1($this->_app_key.$data);
	
	// encrypt cookie data
	$this->rc4->crypt($data);
	
	// make encrypted data cookie safe and encode it with base64
	$data = base64_encode($data);
	
	// set cookie with encrypted data
	$response_one = setcookie($this->_cookie_name, $data, gmmktime() + $this->_expiration, $this->_path);
	
	// set cookie with fingerprint
	$response_two = setcookie("_fingerprint_".$this->_cookie_name, $fingerprint, gmmktime() + $this->_expiration, $this->_path);
	
	if ($response_one && $response_two) {
		return true;
	} else {
		return false;
	}
}

/** 
 * Reads data from cookie and stores retrived data to Base_Cookie::_data.
 * If the received cookie data is tainted, the method will either return
 * boolean false or throw an exception -- behaviour depends on
 * Base_Cookie:_taint_silent.
 *
 * @throws Base_CookieException
 * @return bool
 */ 
protected function readCookieData ()
{
	// make sure that there is some app key for decryption
	if (empty($this->_app_key) || !is_scalar($this->_app_key)) {
		throw new Base_CookieException("No useable application key specified");
	}
	
	// use current app key
	$this->rc4->setKey($this->_app_key);
	
	// get cookie data
	$data = Base_Cnc::ifsetor($_COOKIE[$this->_cookie_name], null);
	
	// decode base64'ed cookie data
	$data = base64_decode($data);
	
	// decrypt cookie data
	$this->rc4->decrypt($data);
	
	// let's see if the fingerprint matches the decrypted data
	if (Base_Cnc::ifsetor($_COOKIE["_fingerprint_".$this->_cookie_name], null) !== sha1($this->_app_key.$data)) {
		// if we should silently fail, set data to null an return boolean false. throw exception otherwise. 
		if ($this->_taint_silent) {
			// set data to null because we can't trust the input
			$this->_data = null;
			
			// return false to signalise that something went wrong
			return false;
		} else {
			throw new Base_CookieException("Received cookie data seems to be tainted");
		}
	}
	
	// unserialize cookie data
	$this->_data = unserialize($data);
	$this->_initial_data = $this->_data;
	
	// everything's ok, return true
	return true;
}

/**
 * Deletes cookie.
 */
public function deleteCookie ()
{
	// delete cookie
	setcookie($this->_cookie_name, null, 0, $this->_path);
	
	// delete fingerprint cookie
	setcookie("_fingerprint_".$this->_cookie_name, null, 0, $this->_path);
}

/**
 * Sets new value for cookie expiration offset to GMT. Takes the new offset
 * as first argument. Returns new expiration offset.
 *
 * @param int Offset in seconds
 * @return int Offset in seconds
 */
public function setCookieExpiration ($offset)
{
	// set new expiration offset
	$this->_expiration = (int)$offset;
	
	// return new expiration offset
	return $this->_expiration;
}

/**
 * Sets new cookie path. Takes a string with the new cookie path as first
 * argument. Returns new cookie path.
 *
 * @param string Cookie path
 * @return string Cookie path
 */
public function setCookiePath ($path)
{
	// set new path
	$this->_path = (string)"/";
	
	// return new path
	return $this->_path;
}

/**
 * Sets new state for Base_Cookie::_taint_silent. Takes boolean state as
 * first argument. Returns new boolean state.
 *
 * @param bool
 * @return bool
 */
public function setTaintSilent ($state)
{
	// set new state
	$this->_taint_silent = (bool)$state;
	
	// return new state
	return $this->_taint_silent;
}

// end of class
}

class Base_CookieException extends Exception {}

?>